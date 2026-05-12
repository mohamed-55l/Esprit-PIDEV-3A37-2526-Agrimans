<?php

namespace App\Tests\Controller;

use App\Controller\StatisticsController;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour StatisticsController.
 *
 * Ces tests vérifient les calculs métier internes du contrôleur
 * en appelant ses méthodes privées via Reflection (technique standard
 * pour tester des méthodes non publiques sans les exposer).
 *
 * Règles métier testées :
 *  1. Le regroupement par catégorie compte correctement les produits.
 *  2. Le tri décroissant est appliqué aux catégories (plus gros groupe en premier).
 *  3. Les produits avec stock < 50 sont détectés comme "low stock".
 *  4. Les produits avec stock < 10 sont marqués "critical" (alerte rouge).
 *  5. La valeur totale du stock = Σ(prix × quantité) pour tous les produits.
 *  6. Le prix moyen par catégorie est calculé correctement.
 *
 * Aucune base de données ni conteneur Symfony n'est nécessaire :
 * on instancie le contrôleur directement et on lui injecte des
 * ProductRepository mockés.
 */
class StatisticsControllerTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper — Reflection pour tester les méthodes privées
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Permet d'appeler une méthode privée du contrôleur.
     * Exemple : $this->callPrivate($ctrl, 'getLowStockProducts', [$repo])
     */
    private function callPrivate(object $object, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);
        return $reflection->invoke($object, ...$args);
    }

    /**
     * Crée un stub Product avec les valeurs fournies.
     */
    private function makeProduct(
        string $name,
        float  $price,
        int    $quantity,
        string $category,
        float  $avgRating = 0.0
    ): Product {
        $p = $this->createMock(Product::class);
        $p->method('getName')->willReturn($name);
        $p->method('getPrice')->willReturn($price);
        $p->method('getQuantity')->willReturn($quantity);
        $p->method('getCategory')->willReturn($category);
        $p->method('getAverageRating')->willReturn($avgRating);
        $p->method('getRatings')->willReturn(new ArrayCollection([]));
        return $p;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1 : Regroupement par catégorie — comptage correct
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Chaque produit doit être compté dans sa catégorie.
     * Si 3 produits sont VEGETABLES et 2 sont FRUITS, le tableau résultant
     * doit refléter ces chiffres exacts.
     */
    public function testGetProductsByCategoryCountsCorrectly(): void
    {
        // Arrange — 3 légumes, 2 fruits
        $products = [
            $this->makeProduct('Tomates',    3.50, 50, 'VEGETABLES'),
            $this->makeProduct('Poivrons',   4.20, 30, 'VEGETABLES'),
            $this->makeProduct('Courgettes', 2.80, 40, 'VEGETABLES'),
            $this->makeProduct('Pommes',     2.00, 80, 'FRUITS'),
            $this->makeProduct('Oranges',    1.80, 60, 'FRUITS'),
        ];

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn($products);

        $controller = new StatisticsController();

        // Act — on appelle la méthode privée via Reflection
        $result = $this->callPrivate($controller, 'getProductsByCategory', [$productRepo]);

        // Assert
        $this->assertArrayHasKey('VEGETABLES', $result,
            'La catégorie VEGETABLES doit exister dans le résultat.');
        $this->assertArrayHasKey('FRUITS', $result,
            'La catégorie FRUITS doit exister dans le résultat.');
        $this->assertSame(3, $result['VEGETABLES'],
            'Il doit y avoir exactement 3 produits VEGETABLES.');
        $this->assertSame(2, $result['FRUITS'],
            'Il doit y avoir exactement 2 produits FRUITS.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2 : Tri décroissant — la catégorie la plus peuplée est en premier
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Les catégories doivent être triées du plus grand
     * au plus petit nombre de produits (arsort).
     * Cela permet un affichage des statistiques en ordre pertinent.
     */
    public function testGetProductsByCategoryIsSortedDescending(): void
    {
        // Arrange — 1 FRUIT, 4 VEGETABLES
        $products = [
            $this->makeProduct('Tomate',    3.0, 50, 'VEGETABLES'),
            $this->makeProduct('Oignon',    1.5, 60, 'VEGETABLES'),
            $this->makeProduct('Carotte',   2.0, 70, 'VEGETABLES'),
            $this->makeProduct('Aubergine', 2.5, 40, 'VEGETABLES'),
            $this->makeProduct('Pomme',     2.0, 80, 'FRUITS'),
        ];

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn($products);

        $controller = new StatisticsController();

        // Act
        $result = $this->callPrivate($controller, 'getProductsByCategory', [$productRepo]);
        $firstKey = array_key_first($result);

        // Assert
        $this->assertSame('VEGETABLES', $firstKey,
            'VEGETABLES (4 produits) doit apparaître en premier (tri décroissant).');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3 : Détection stock faible (< 50 kg)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Tout produit avec moins de 50 kg en stock
     * est considéré comme "low stock" et doit apparaître dans la liste d'alerte.
     */
    public function testGetLowStockProductsDetectsProductsBelowThreshold(): void
    {
        // Arrange — 1 produit en stock faible (20 kg), 1 normal (100 kg)
        $lowProduct  = $this->makeProduct('Safran',  50.0, 20, 'SPICES');
        $okProduct   = $this->makeProduct('Tomates',   3.5, 100, 'VEGETABLES');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn([$lowProduct, $okProduct]);

        $controller = new StatisticsController();

        // Act
        $result = $this->callPrivate($controller, 'getLowStockProducts', [$productRepo]);

        // Assert
        $this->assertCount(1, $result,
            'Seul le produit à 20 kg (< 50) doit être dans la liste low stock.');
        $this->assertSame('Safran', $result[0]['product']->getName(),
            'Le produit low stock doit être "Safran".');
        $this->assertSame('low', $result[0]['status'],
            'Avec 20 kg (≥ 10), le statut doit être "low".');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4 : Statut "critical" pour stock < 10 kg
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Si le stock est inférieur à 10 kg, le produit est
     * en état "critical" (alerte rouge — rupture imminente).
     */
    public function testGetLowStockProductsMarksCriticalWhenBelow10(): void
    {
        // Arrange — stock = 5 kg → critique
        $critical = $this->makeProduct('Truffe', 200.0, 5, 'SPICES');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn([$critical]);

        $controller = new StatisticsController();

        // Act
        $result = $this->callPrivate($controller, 'getLowStockProducts', [$productRepo]);

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('critical', $result[0]['status'],
            'Un stock de 5 kg (< 10) doit être marqué "critical".');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5 : Valeur totale du stock = Σ(prix × quantité)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : La valeur totale en stock est calculée en multipliant
     * le prix de chaque produit par sa quantité, puis en faisant la somme.
     * Exemple : (3.00 × 50) + (5.00 × 20) = 150 + 100 = 250 TND
     */
    public function testGetTotalStockValueCalculatesCorrectly(): void
    {
        // Arrange
        $p1 = $this->makeProduct('Tomates', 3.00, 50, 'VEGETABLES'); // 150 TND
        $p2 = $this->makeProduct('Miel',    5.00, 20, 'HONEY');       // 100 TND

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn([$p1, $p2]);

        $controller = new StatisticsController();

        // Act
        $result = $this->callPrivate($controller, 'getTotalStockValue', [$productRepo]);

        // Assert
        $this->assertSame(250.0, $result,
            'La valeur totale (3×50 + 5×20 = 250 TND) doit être correcte.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 6 : Prix moyen par catégorie
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Le prix moyen par catégorie est calculé en divisant
     * la somme des prix par le nombre de produits de cette catégorie.
     * Exemple : FRUITS → (2.00 + 4.00) / 2 = 3.00 TND
     */
    public function testGetAveragePriceByCategoryIsCorrect(): void
    {
        // Arrange — 2 fruits à prix 2.00 et 4.00
        $p1 = $this->makeProduct('Pomme',  2.00, 100, 'FRUITS');
        $p2 = $this->makeProduct('Mangue', 4.00,  50, 'FRUITS');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('findAll')->willReturn([$p1, $p2]);

        $controller = new StatisticsController();

        // Act
        $result = $this->callPrivate($controller, 'getAveragePriceByCategory', [$productRepo]);

        // Assert
        $this->assertArrayHasKey('FRUITS', $result,
            'La catégorie FRUITS doit être présente dans les prix moyens.');
        $this->assertSame(3.0, $result['FRUITS'],
            'Le prix moyen FRUITS ((2.00 + 4.00) / 2 = 3.00) doit être correct.');
    }
}
