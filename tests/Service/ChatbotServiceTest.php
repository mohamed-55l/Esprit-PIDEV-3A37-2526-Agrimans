<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\ChatbotService;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour ChatbotService.
 *
 * Règles métier testées :
 *  1. Une salutation retourne le type "greeting" sans produits.
 *  2. La requête "légumes disponibles" mappe vers la catégorie VEGETABLES.
 *  3. La requête "fruits" mappe vers la catégorie FRUITS.
 *  4. Une requête vide retourne une invite d'aide de type "greeting".
 *  5. Une requête de prix "moins de 5" filtre les produits par prix.
 *  6. Une recherche sans résultat retourne le type "no_results".
 *  7. Une requête fournisseur sans nom de produit retourne une demande de clarification.
 *
 * Tous les dépôts (ProductRepository, UserRepository) sont mockés :
 * aucune base de données n'est sollicitée.
 */
class ChatbotServiceTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Test 1 : Salutation → type "greeting", aucun produit
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Quand l'utilisateur envoie une salutation (ex. "bonjour"),
     * le chatbot doit répondre avec le type "greeting" et une liste vide de produits.
     * Aucun appel au repository n'est attendu.
     */
    public function testGreetingQueryReturnsGreetingType(): void
    {
        // Arrange — les repositories ne doivent jamais être appelés pour un bonjour
        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->never())->method('search');
        $productRepo->expects($this->never())->method('findByCategory');

        $userRepo = $this->createMock(UserRepository::class);

        $service = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('bonjour');

        // Assert
        $this->assertSame('greeting', $result['type'],
            'Une salutation doit retourner le type "greeting".');
        $this->assertIsArray($result['products']);
        $this->assertEmpty($result['products'],
            'Une salutation ne doit pas retourner de produits.');
        $this->assertNotEmpty($result['response'],
            'La réponse de salutation ne doit pas être vide.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2 : "légumes disponibles" → catégorie VEGETABLES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Le mot-clé "légume" doit être reconnu et mappé vers
     * la catégorie base de données "VEGETABLES".
     * findByCategory doit être appelé exactement une fois avec "VEGETABLES".
     */
    public function testCategoryQueryLegumesMapsToVegetables(): void
    {
        // Arrange — deux produits légumes fictifs
        $tomate  = $this->makeProduct(1, 'Tomates Bio',     3.50, 50, 'VEGETABLES');
        $poivron = $this->makeProduct(2, 'Poivrons Rouges', 4.20, 30, 'VEGETABLES');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->once())
                    ->method('findByCategory')
                    ->with('VEGETABLES')
                    ->willReturn([$tomate, $poivron]);

        // search() ne doit pas être appelé pour une requête de catégorie pure
        $productRepo->expects($this->never())->method('search');

        $userRepo = $this->createMock(UserRepository::class);
        $service  = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('légumes disponibles');

        // Assert
        $this->assertSame('category_search', $result['type'],
            '"légumes disponibles" doit retourner le type "category_search".');
        $this->assertCount(2, $result['products'],
            'Les deux produits VEGETABLES doivent être retournés.');
        $this->assertStringContainsStringIgnoringCase('légume',
            strtolower($result['response']),
            'La réponse doit mentionner les légumes.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3 : "fruits" → catégorie FRUITS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Le mot-clé "fruits" doit être mappé vers "FRUITS".
     */
    public function testCategoryQueryFruitsMapsToFruits(): void
    {
        // Arrange
        $pomme   = $this->makeProduct(3, 'Pommes Golden', 2.80, 100, 'FRUITS');
        $banane  = $this->makeProduct(4, 'Bananes',       1.50,  80, 'FRUITS');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->once())
                    ->method('findByCategory')
                    ->with('FRUITS')
                    ->willReturn([$pomme, $banane]);

        $userRepo = $this->createMock(UserRepository::class);
        $service  = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('fruits');

        // Assert
        $this->assertSame('category_search', $result['type'],
            '"fruits" doit retourner le type "category_search".');
        $this->assertCount(2, $result['products']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4 : Requête vide → invite d'aide de type "greeting"
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Une requête vide doit retourner une invite d'aide
     * (type "greeting") avec une liste vide de produits.
     */
    public function testEmptyQueryReturnsHelpGreeting(): void
    {
        // Arrange
        $productRepo = $this->createMock(ProductRepository::class);
        $userRepo    = $this->createMock(UserRepository::class);
        $service     = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('');

        // Assert
        $this->assertSame('greeting', $result['type'],
            'Une requête vide doit retourner le type "greeting".');
        $this->assertEmpty($result['products']);
        $this->assertStringContainsStringIgnoringCase('bonjour',
            strtolower($result['response']),
            'La réponse vide doit contenir une salutation.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5 : Prix "moins de 5" → filtre les produits > 5 TND
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : La phrase "moins de 5" extrait le plafond de prix (5 TND)
     * et filtre tous les produits dont le prix dépasse ce plafond.
     */
    public function testPriceQueryFiltersProductsAboveMaxPrice(): void
    {
        // Arrange — un produit à 3 TND (ok) et un à 8 TND (éliminé)
        $cheap     = $this->makeProduct(5, 'Oignons',  3.00, 200, 'VEGETABLES');
        $expensive = $this->makeProduct(6, 'Truffe',   8.00,  10, 'VEGETABLES');

        $productRepo = $this->createMock(ProductRepository::class);
        // Pour "moins de 5" sans autre mot-clé, le service appelle findAll()
        $productRepo->method('findAll')->willReturn([$cheap, $expensive]);

        $userRepo = $this->createMock(UserRepository::class);
        $service  = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('moins de 5');

        // Assert
        $this->assertSame('price_search', $result['type'],
            '"moins de 5" doit retourner le type "price_search".');
        $this->assertCount(1, $result['products'],
            'Seul le produit à 3 TND doit être retenu (sous le seuil de 5 TND).');
        $this->assertSame('Oignons', $result['products'][0]->getName(),
            'Le produit retenu doit être "Oignons".');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 6 : Recherche sans résultat → type "no_results"
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Si la recherche ne trouve aucun produit correspondant,
     * le type retourné doit être "no_results" avec une liste vide.
     */
    public function testSearchWithNoResultsReturnsNoResultsType(): void
    {
        // Arrange
        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('search')->willReturn([]); // aucun résultat

        $userRepo = $this->createMock(UserRepository::class);
        $service  = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('produit inexistant xyz');

        // Assert
        $this->assertSame('no_results', $result['type'],
            'Une recherche sans résultat doit retourner le type "no_results".');
        $this->assertEmpty($result['products']);
        $this->assertStringContainsStringIgnoringCase('aucun', $result['response'],
            'La réponse doit indiquer qu\'aucun produit n\'a été trouvé.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 7 : Requête fournisseur sans nom de produit → clarification
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Si l'utilisateur demande un fournisseur sans préciser
     * le nom du produit, le chatbot doit demander une clarification.
     */
    public function testSupplierQueryWithoutProductNameReturnsClarification(): void
    {
        // Arrange
        $productRepo = $this->createMock(ProductRepository::class);
        $userRepo    = $this->createMock(UserRepository::class);
        $service     = new ChatbotService($productRepo, $userRepo);

        // Act — "fournisseur" seul, sans nom de produit
        $result = $service->processQuery('fournisseur');

        // Assert
        $this->assertSame('clarification', $result['type'],
            'Une requête fournisseur sans produit doit retourner "clarification".');
        $this->assertEmpty($result['products']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper — crée un stub Product minimal
    // ─────────────────────────────────────────────────────────────────────────

    private function makeProduct(int $id, string $name, float $price, int $qty, string $category): Product
    {
        $p = $this->createMock(Product::class);
        $p->method('getId')->willReturn($id);
        $p->method('getName')->willReturn($name);
        $p->method('getPrice')->willReturn($price);
        $p->method('getQuantity')->willReturn($qty);
        $p->method('getCategory')->willReturn($category);
        $p->method('getSupplier')->willReturn(null);
        return $p;
    }
}
