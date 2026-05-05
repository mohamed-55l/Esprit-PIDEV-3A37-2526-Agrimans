<?php

namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;

class ChatbotService
{
    private const GREETING_KEYWORDS = ['bonjour', 'hello', 'hi', 'salut', 'ça va', 'comment', 'aide'];
    private const PRICE_KEYWORDS    = ['prix', 'coût', 'coûte', 'combien', 'tarif', 'price', 'expensive', 'cheap'];
    private const SUPPLIER_KEYWORDS = ['fournisseur', 'supplier', 'vendeur', 'seller', 'qui vend', 'meilleur'];

    /**
     * Maps any user-typed keyword to the real DB category value.
     */
    private const CATEGORY_MAP = [
        'légume'    => 'VEGETABLES',
        'legume'    => 'VEGETABLES',
        'vegetable' => 'VEGETABLES',
        'vegetables'=> 'VEGETABLES',
        'fruit'     => 'FRUITS',
        'fruits'    => 'FRUITS',
        'viande'    => 'MEAT',
        'meat'      => 'MEAT',
        'laitier'   => 'DAIRY',
        'dairy'     => 'DAIRY',
        'lait'      => 'DAIRY',
        'céréale'   => 'CEREALS',
        'cereale'   => 'CEREALS',
        'cereal'    => 'CEREALS',
        'épice'     => 'SPICES',
        'epice'     => 'SPICES',
        'spice'     => 'SPICES',
        'huile'     => 'OLIVE_OIL',
        'olive'     => 'OLIVE_OIL',
        'miel'      => 'HONEY',
        'honey'     => 'HONEY',
    ];

    public function __construct(
        private ProductRepository $productRepository,
        private UserRepository $userRepository
    ) {}

    public function processQuery(string $query): array
    {
        $query = trim(strtolower($query));

        // Remove accents for more flexible matching
        $normalized = $this->removeAccents($query);

        // Empty query
        if (empty($query)) {
            return [
                'response' => 'Bonjour! 👋 Vous pouvez me poser des questions sur nos produits. Par exemple: "Légumes disponibles", "Fruits moins de 5 TND", "Cherche tomates". Comment puis-je vous aider?',
                'products' => [],
                'type'     => 'greeting',
            ];
        }

        // Greeting check
        foreach (self::GREETING_KEYWORDS as $keyword) {
            if (str_contains($normalized, $this->removeAccents($keyword))) {
                return [
                    'response' => 'Bienvenue chez Agrimans! 🌾 Je suis votre assistant pour les produits agricoles. Demandez-moi n\'importe quel produit, le prix ou le fournisseur!',
                    'products' => [],
                    'type'     => 'greeting',
                ];
            }
        }

        // Supplier check
        if ($this->containsKeywords($normalized, array_map([$this, 'removeAccents'], self::SUPPLIER_KEYWORDS))) {
            return $this->handleSupplierQuery($query);
        }

        // Price check — detect "moins de X" OR standard price keywords
        $hasPricePattern = (bool) preg_match('/moins de\s+\d+|under\s+\d+|<\s*\d+/i', $query);
        $hasPriceKeyword = $this->containsKeywords($normalized, self::PRICE_KEYWORDS);

        if ($hasPricePattern || $hasPriceKeyword) {
            return $this->handlePriceQuery($query);
        }

        // Category check — "légumes disponibles", "fruits", "viande" …
        $detectedCategory = $this->detectCategory($normalized);
        if ($detectedCategory !== null) {
            return $this->handleCategoryQuery($detectedCategory, $query);
        }

        // Default: full-text product search
        return $this->handleProductSearch($query);
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Detect if the query refers to a product category and return the DB value.
     */
    private function detectCategory(string $normalizedQuery): ?string
    {
        foreach (self::CATEGORY_MAP as $keyword => $dbCategory) {
            if (str_contains($normalizedQuery, $this->removeAccents($keyword))) {
                return $dbCategory;
            }
        }
        return null;
    }

    private function handleCategoryQuery(string $category, string $originalQuery): array
    {
        $products = $this->productRepository->findByCategory($category);

        $categoryLabels = [
            'VEGETABLES' => 'Légumes',
            'FRUITS'     => 'Fruits',
            'MEAT'       => 'Viandes',
            'DAIRY'      => 'Produits Laitiers',
            'CEREALS'    => 'Céréales',
            'SPICES'     => 'Épices',
            'OLIVE_OIL'  => 'Huile d\'Olive',
            'HONEY'      => 'Miel',
        ];

        $label = $categoryLabels[$category] ?? $category;

        if (empty($products)) {
            return [
                'response' => sprintf('❌ Aucun produit trouvé dans la catégorie **%s** pour le moment.', $label),
                'products' => [],
                'type'     => 'no_results',
            ];
        }

        $limited = array_slice($products, 0, 6);
        $count   = count($products);

        $response = sprintf('🥬 **%d %s** disponible%s :\\n\\n', $count, $label, $count > 1 ? 's' : '');
        foreach ($limited as $p) {
            $response .= sprintf("• **%s** — %.2f TND (%.0f kg en stock)\\n", $p->getName(), $p->getPrice(), $p->getQuantity());
        }
        if ($count > 6) {
            $response .= sprintf('\\n... et %d autre(s). Consultez la boutique!', $count - 6);
        }

        return [
            'response' => $response,
            'products' => $limited,
            'type'     => 'category_search',
        ];
    }

    private function handleProductSearch(string $query): array
    {
        // Strip common filler words that break search
        $fillers = ['disponibles', 'disponible', 'cherche', 'trouver', 'trouve', 'montre', 'voir', 'liste', 'tous', 'les'];
        $cleaned = $query;
        foreach ($fillers as $filler) {
            $cleaned = str_ireplace($filler, '', $cleaned);
        }
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        if (empty($cleaned)) {
            $products = $this->productRepository->findAll();
        } else {
            $products = $this->productRepository->search($cleaned, null);
        }

        if (empty($products)) {
            return [
                'response' => sprintf(
                    '❌ Désolé, je n\'ai trouvé aucun produit pour "%s". Essayez: "légumes", "tomates", "miel"…',
                    ucfirst($query)
                ),
                'products' => [],
                'type'     => 'no_results',
            ];
        }

        $limited = array_slice($products, 0, 5);
        $count   = count($products);

        $response = sprintf('✅ %d produit%s trouvé%s pour "%s":\\n\\n', min($count, 5), $count > 1 ? 's' : '', $count > 1 ? 's' : '', ucfirst($cleaned));
        foreach ($limited as $p) {
            $response .= sprintf("🛒 **%s** — %.2f TND\\n", $p->getName(), $p->getPrice());
        }
        if ($count > 5) {
            $response .= sprintf('\\n... et %d autre(s) dans la boutique!', $count - 5);
        }

        return [
            'response' => $response,
            'products' => $limited,
            'type'     => 'search',
        ];
    }

    private function handlePriceQuery(string $query): array
    {
        $maxPrice = null;
        $minPrice = null;
        $remaining = $query;

        // Extract "moins de X" / "under X"
        if (preg_match('/moins de\s+(\d+(?:[.,]\d+)?)/i', $query, $m)) {
            $maxPrice  = (float) str_replace(',', '.', $m[1]);
            $remaining = str_replace($m[0], '', $remaining);
        } elseif (preg_match('/under\s+(\d+(?:[.,]\d+)?)/i', $query, $m)) {
            $maxPrice  = (float) str_replace(',', '.', $m[1]);
            $remaining = str_replace($m[0], '', $remaining);
        }

        // Strip price units and noise
        $remaining = preg_replace('/\d+(?:[.,]\d+)?\s*(tnd|dt|dinar)?/i', '', $remaining);
        $remaining = trim(preg_replace('/\s+/', ' ', $remaining));

        // Strip price keywords
        foreach (array_merge(self::PRICE_KEYWORDS, ['tnd', 'dt']) as $kw) {
            $remaining = str_ireplace($kw, '', $remaining);
        }
        $remaining = trim(preg_replace('/\s+/', ' ', $remaining));

        // Check if remaining maps to a category
        $normalizedRemaining = $this->removeAccents(strtolower($remaining));
        $detectedCategory    = $this->detectCategory($normalizedRemaining);

        // Fetch products
        if ($detectedCategory) {
            $products = $this->productRepository->findByCategory($detectedCategory);
        } elseif (!empty($remaining)) {
            $products = $this->productRepository->search($remaining, null);
        } else {
            $products = $this->productRepository->findAll();
        }

        // Filter by price
        if ($maxPrice !== null) {
            $products = array_values(array_filter($products, fn($p) => $p->getPrice() <= $maxPrice));
        }
        if ($minPrice !== null) {
            $products = array_values(array_filter($products, fn($p) => $p->getPrice() >= $minPrice));
        }

        if (empty($products)) {
            return [
                'response' => $maxPrice !== null
                    ? sprintf('❌ Aucun produit trouvé à moins de %.2f TND.', $maxPrice)
                    : '❌ Aucun produit trouvé.',
                'products' => [],
                'type'     => 'no_results',
            ];
        }

        usort($products, fn($a, $b) => $a->getPrice() <=> $b->getPrice());
        $limited = array_slice($products, 0, 5);
        $count   = count($products);

        $label = $maxPrice !== null ? "moins de $maxPrice TND" : 'disponibles';
        $response = sprintf('💰 **%d produit%s** %s :\\n\\n', $count, $count > 1 ? 's' : '', $label);
        foreach ($limited as $p) {
            $response .= sprintf("🛒 **%s** — **%.2f TND** (%.0f kg dispo)\\n", $p->getName(), $p->getPrice(), $p->getQuantity());
        }
        if ($count > 5) {
            $response .= sprintf('\\n... et %d autre(s). Voir la boutique!', $count - 5);
        }

        return [
            'response' => $response,
            'products' => $limited,
            'type'     => 'price_search',
        ];
    }

    private function handleSupplierQuery(string $query): array
    {
        $stopWords   = ['fournisseur', 'supplier', 'vendeur', 'seller', 'qui vend', 'meilleur', 'best', 'du', 'de', 'de la', 'le', 'les', 'un', 'une'];
        $productName = str_ireplace($stopWords, '', $query);
        $productName = trim(preg_replace('/\s+/', ' ', $productName));

        if (empty($productName)) {
            return [
                'response' => '❓ Quel produit vous intéresse? Dites-moi le nom du produit et je trouverai le meilleur fournisseur!',
                'products' => [],
                'type'     => 'clarification',
            ];
        }

        $products = $this->productRepository->search($productName, null);

        if (empty($products)) {
            return [
                'response' => sprintf('❌ Je n\'ai pas trouvé "%s". Essayez avec un autre nom!', ucfirst($productName)),
                'products' => [],
                'type'     => 'no_results',
            ];
        }

        $suppliers = [];
        foreach ($products as $p) {
            if ($p->getSupplier()) {
                $suppliers[$p->getSupplier()][] = ['product' => $p->getName(), 'price' => $p->getPrice()];
            }
        }

        if (empty($suppliers)) {
            return [
                'response' => sprintf('❌ Pas de fournisseur disponible pour "%s".', ucfirst($productName)),
                'products' => [],
                'type'     => 'no_results',
            ];
        }

        $response = sprintf("👨‍🌾 Fournisseur(s) pour **%s** :\\n\\n", ucfirst($productName));
        foreach ($suppliers as $name => $items) {
            $avg = array_sum(array_column($items, 'price')) / count($items);
            $response .= sprintf("⭐ **%s** — Moy: %.2f TND (%d produit%s)\\n", $name, $avg, count($items), count($items) > 1 ? 's' : '');
        }

        return [
            'response' => $response,
            'products' => array_slice($products, 0, 5),
            'type'     => 'supplier',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function containsKeywords(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function removeAccents(string $str): string
    {
        $from = ['à','â','ä','á','ã','å','è','é','ê','ë','ì','í','î','ï','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','ç','ñ','À','Â','Ä','Á','Ã','Å','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý','Ç','Ñ'];
        $to   = ['a','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','y','y','c','n','A','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','Y','C','N'];
        return str_replace($from, $to, $str);
    }
}
