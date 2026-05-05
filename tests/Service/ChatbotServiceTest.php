<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\ChatbotService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ChatbotService.
 * Repositories are mocked — no database required.
 */
class ChatbotServiceTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Test 1: Greeting detection
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * When the user sends a greeting ("bonjour"), the chatbot
     * must return a response of type 'greeting' with an empty products list.
     */
    public function testGreetingQueryReturnsGreetingType(): void
    {
        // Arrange — repositories are never called for a greeting
        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->never())->method('search');
        $productRepo->expects($this->never())->method('findByCategory');

        $userRepo = $this->createMock(UserRepository::class);

        $service = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('bonjour');

        // Assert
        $this->assertSame('greeting', $result['type'],
            'Une salutation doit retourner le type "greeting"');
        $this->assertIsArray($result['products']);
        $this->assertEmpty($result['products'],
            'Une salutation ne doit pas retourner de produits');
        $this->assertNotEmpty($result['response'],
            'La réponse de salutation ne doit pas être vide');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2: Category query — "légumes disponibles"
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * When the user asks for "légumes disponibles", the chatbot must:
     *  - call findByCategory('VEGETABLES') on the repository
     *  - return the type 'category_search'
     *  - include the returned products in the response
     */
    public function testCategoryQueryLegumesMapsToVegetables(): void
    {
        // Arrange — build two fake Product objects
        $tomate  = $this->makeProduct(1, 'Tomates Bio',     3.50, 50, 'VEGETABLES');
        $poivron = $this->makeProduct(2, 'Poivrons Rouges', 4.20, 30, 'VEGETABLES');

        $productRepo = $this->createMock(ProductRepository::class);

        // findByCategory MUST be called with 'VEGETABLES'
        $productRepo->expects($this->once())
            ->method('findByCategory')
            ->with('VEGETABLES')
            ->willReturn([$tomate, $poivron]);

        // search() should NOT be called for a pure category query
        $productRepo->expects($this->never())->method('search');

        $userRepo = $this->createMock(UserRepository::class);

        $service = new ChatbotService($productRepo, $userRepo);

        // Act
        $result = $service->processQuery('légumes disponibles');

        // Assert
        $this->assertSame('category_search', $result['type'],
            'La requête "légumes disponibles" doit retourner le type "category_search"');
        $this->assertCount(2, $result['products'],
            'Les deux produits VEGETABLES doivent être dans la réponse');
        $this->assertStringContainsStringIgnoringCase('légume', strtolower($result['response']),
            'La réponse doit mentionner la catégorie Légumes');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper — create a minimal Product stub
    // ─────────────────────────────────────────────────────────────────────────

    private function makeProduct(int $id, string $name, float $price, int $qty, string $category): Product
    {
        $p = $this->createMock(Product::class);
        $p->method('getId')->willReturn($id);
        $p->method('getName')->willReturn($name);
        $p->method('getPrice')->willReturn($price);
        $p->method('getQuantity')->willReturn($qty);
        $p->method('getCategory')->willReturn($category);
        return $p;
    }
}
