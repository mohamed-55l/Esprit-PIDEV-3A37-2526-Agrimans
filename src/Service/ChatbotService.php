<?php

namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;

class ChatbotService
{
    private const GREETING_KEYWORDS = ['bonjour', 'hello', 'hi', 'salut', 'ça va', 'comment', 'aide'];
    private const PRICE_KEYWORDS = ['prix', 'coût', 'coûte', 'combien', 'tarif', 'price', 'expensive', 'cheap'];
    private const CATEGORY_KEYWORDS = ['légume', 'legume', 'fruit', 'viande', 'laitier', 'dairy', 'meat', 'vegetable'];
    private const SUPPLIER_KEYWORDS = ['fournisseur', 'supplier', 'vendeur', 'seller', 'qui vend', 'meilleur'];

    public function __construct(
        private ProductRepository $productRepository,
        private UserRepository $userRepository
    ) {}

    public function processQuery(string $query): array
    {
        $query = trim(strtolower($query));

        // Empty query
        if (empty($query)) {
            return [
                'response' => 'Bonjour! 👋 Vous pouvez me poser des questions sur nos produits. Par exemple: "Cherche tomates", "Fruits moins de 5 TND", "Quel fournisseur vend du miel?" Comment puis-je vous aider?',
                'products' => [],
                'type' => 'greeting'
            ];
        }

        // Check if it's a greeting
        foreach (self::GREETING_KEYWORDS as $keyword) {
            if (str_contains($query, $keyword)) {
                return [
                    'response' => 'Bienvenue chez Agrimans! 🌾 Je suis votre assistant pour les produits agricoles. Demandez-moi n\'importe quel produit, le prix ou le fournisseur!',
                    'products' => [],
                    'type' => 'greeting'
                ];
            }
        }

        // Check for supplier query
        if ($this->containsKeywords($query, self::SUPPLIER_KEYWORDS)) {
            return $this->handleSupplierQuery($query);
        }

        // Check for price query
        if ($this->containsKeywords($query, self::PRICE_KEYWORDS)) {
            return $this->handlePriceQuery($query);
        }

        // Default: product search by name
        return $this->handleProductSearch($query);
    }

    private function containsKeywords(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function handleProductSearch(string $query): array
    {
        $searchTerms = array_filter(explode(' ', $query));
        $products = $this->productRepository->search(implode(' ', $searchTerms), null);

        if (empty($products)) {
            return [
                'response' => sprintf(
                    '❌ Désolé, je n\'ai trouvé aucun produit correspondant à "%s". Essayez un autre terme ou explorez notre catalogue!',
                    ucfirst(implode(' ', $searchTerms))
                ),
                'products' => [],
                'type' => 'no_results'
            ];
        }

        $limitedProducts = array_slice($products, 0, 5);
        $count = count($products);

        $response = sprintf(
            '✅ Trouvé %d produit%s correspondant à "%s":\n\n',
            min($count, 5),
            $count > 1 ? 's' : '',
            ucfirst(implode(' ', $searchTerms))
        );

        foreach ($limitedProducts as $product) {
            $response .= sprintf(
                "🥬 **%s** - %.2f TND\n",
                $product->getName(),
                $product->getPrice()
            );
        }

        if ($count > 5) {
            $response .= sprintf("\n... et %d autre(s) produit(s). Consultez la boutique pour plus!", $count - 5);
        }

        return [
            'response' => $response,
            'products' => $limitedProducts,
            'type' => 'search'
        ];
    }

    private function handlePriceQuery(string $query): array
    {
        // Extract price range from query
        $maxPrice = null;
        $minPrice = null;
        $productName = $query;

        // Try to extract "moins de X" or "under X"
        if (preg_match('/moins de\s+(\d+(?:,\d+)?)/i', $query, $matches)) {
            $maxPrice = (float) str_replace(',', '.', $matches[1]);
            $productName = str_replace($matches[0], '', $query);
        } elseif (preg_match('/moins de\s+(\d+(?:\.\d+)?)/i', $query, $matches)) {
            $maxPrice = (float) $matches[1];
            $productName = str_replace($matches[0], '', $query);
        }

        $productName = trim(array_filter(explode(' ', $productName))[0] ?? '');

        // Get products
        if (!empty($productName)) {
            $products = $this->productRepository->search($productName, null);
        } else {
            $products = $this->productRepository->findAll();
        }

        // Filter by price
        if ($maxPrice !== null) {
            $products = array_filter($products, fn($p) => $p->getPrice() <= $maxPrice);
        }

        if (empty($products)) {
            return [
                'response' => $maxPrice !== null
                    ? sprintf('❌ Pas de produits trouvés moins de %.2f TND', $maxPrice)
                    : '❌ Aucun produit trouvé',
                'products' => [],
                'type' => 'no_results'
            ];
        }

        $products = array_slice($products, 0, 5);
        usort($products, fn($a, $b) => $a->getPrice() <=> $b->getPrice());

        $response = sprintf(
            '💰 Produits %s:\n\n',
            $maxPrice !== null ? "moins de $maxPrice TND" : 'disponibles'
        );

        foreach ($products as $product) {
            $response .= sprintf(
                "🛒 **%s** - **%.2f TND** (%.0f kg dispo)\n",
                $product->getName(),
                $product->getPrice(),
                $product->getQuantity()
            );
        }

        return [
            'response' => $response,
            'products' => $products,
            'type' => 'price_search'
        ];
    }

    private function handleSupplierQuery(string $query): array
    {
        // Extract product name from query
        $productName = str_replace(
            ['fournisseur', 'supplier', 'vendeur', 'seller', 'qui vend', 'meilleur', 'best', 'du', 'de', 'de la', 'le', 'les', 'un', 'une'],
            '',
            $query
        );
        $productName = trim(preg_replace('/\s+/', ' ', $productName));

        if (empty($productName)) {
            return [
                'response' => '❓ Quel produit vous intéresse? Dites-moi le nom du produit et je vous trouverai le meilleur fournisseur!',
                'products' => [],
                'type' => 'clarification'
            ];
        }

        $products = $this->productRepository->search($productName, null);

        if (empty($products)) {
            return [
                'response' => sprintf(
                    '❌ Je n\'ai pas trouvé "%s". Essayez avec un autre nom!',
                    ucfirst($productName)
                ),
                'products' => [],
                'type' => 'no_results'
            ];
        }

        $response = sprintf("👨‍🌾 Fournisseur(s) pour **%s**:\n\n", ucfirst($productName));

        $suppliers = [];
        foreach ($products as $product) {
            if ($product->getSupplier()) {
                $suppliers[] = [
                    'name' => $product->getSupplier(),
                    'product' => $product->getName(),
                    'price' => $product->getPrice(),
                ];
            }
        }

        if (empty($suppliers)) {
            return [
                'response' => sprintf('❌ Pas de fournisseur disponible pour "%s"', ucfirst($productName)),
                'products' => [],
                'type' => 'no_results'
            ];
        }

        $uniqueSuppliers = [];
        foreach ($suppliers as $supplier) {
            if (!isset($uniqueSuppliers[$supplier['name']])) {
                $uniqueSuppliers[$supplier['name']] = [];
            }
            $uniqueSuppliers[$supplier['name']][] = $supplier;
        }

        foreach ($uniqueSuppliers as $supplierName => $items) {
            $avgPrice = array_sum(array_column($items, 'price')) / count($items);
            $response .= sprintf(
                "⭐ **%s** - Moyenne: %.2f TND (%d produit%s)\n",
                $supplierName,
                $avgPrice,
                count($items),
                count($items) > 1 ? 's' : ''
            );
        }

        return [
            'response' => $response,
            'products' => array_slice($products, 0, 5),
            'type' => 'supplier'
        ];
    }
}
