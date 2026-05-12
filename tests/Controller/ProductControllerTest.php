<?php

namespace App\Tests\Controller;

use App\Controller\ProductController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Tests unitaires pour ProductController.
 *
 * Règles métier testées :
 *  1. setCurrency stocke la devise choisie en session et redirige vers le referer.
 *  2. setCurrency ignore les devises non supportées (la session reste inchangée).
 *  3. setCurrency redirige vers /marketplace si le referer est absent.
 *  4. Seules TND, EUR et USD sont des devises valides.
 *
 * Ces tests vérifient la logique pure du contrôleur sans serveur HTTP,
 * sans base de données et sans conteneur Symfony complet.
 * On utilise un container mocké uniquement pour satisfaire AbstractController
 * (méthode redirect).
 */
class ProductControllerTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper : construit un ProductController avec un conteneur minimal mocké
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * AbstractController::redirect() passe par le routeur via le conteneur.
     * On fournit un mock de ContainerInterface qui retourne un RouterInterface
     * factice pour éviter une exception de service manquant.
     */
    private function buildController(): ProductController
    {
        $controller = new ProductController();

        $router = $this->createMock(\Symfony\Component\Routing\RouterInterface::class);

        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturnCallback(
            fn($id) => in_array($id, ['router', 'request_stack', 'twig'], true)
        );
        $container->method('get')->willReturnCallback(function ($id) use ($router) {
            if ($id === 'router') {
                return $router;
            }
            return null;
        });

        $controller->setContainer($container);
        return $controller;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1 : setCurrency stocke la devise valide en session
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Quand l'utilisateur choisit une devise supportée (EUR),
     * celle-ci doit être enregistrée en session sous la clé "currency".
     * La réponse doit être une redirection HTTP 302 vers le referer.
     */
    public function testSetCurrencyStoresValidCurrencyInSession(): void
    {
        // Arrange
        $controller = $this->buildController();

        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace?page=2');

        // Act
        $response = $controller->setCurrency('EUR', $request);

        // Assert
        $this->assertSame('EUR', $session->get('currency'),
            'La devise EUR doit être enregistrée en session.');
        $this->assertSame(302, $response->getStatusCode(),
            'La réponse doit être une redirection HTTP 302.');
        $this->assertSame('/marketplace?page=2', $response->headers->get('Location'),
            'La redirection doit pointer vers le referer.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2 : setCurrency accepte USD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : USD est une devise supportée et doit être stockée.
     */
    public function testSetCurrencyAcceptsUsd(): void
    {
        // Arrange
        $controller = $this->buildController();

        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace');

        // Act
        $controller->setCurrency('USD', $request);

        // Assert
        $this->assertSame('USD', $session->get('currency'),
            'La devise USD doit être enregistrée en session.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3 : setCurrency ignore une devise non supportée
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Une devise inconnue (ex. "GBP") ne doit pas modifier
     * la valeur de la session. La devise courante reste inchangée.
     * Cela protège l'application contre des valeurs de devise invalides.
     */
    public function testSetCurrencyIgnoresUnsupportedCurrency(): void
    {
        // Arrange
        $controller = $this->buildController();

        $session = new Session(new MockArraySessionStorage());
        $session->set('currency', 'TND'); // devise initiale

        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace');

        // Act
        $response = $controller->setCurrency('GBP', $request);

        // Assert
        $this->assertSame('TND', $session->get('currency'),
            'Une devise non supportée ne doit pas modifier la session.');
        $this->assertSame(302, $response->getStatusCode(),
            'Même pour une devise invalide, on redirige (HTTP 302).');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4 : setCurrency redirige vers /marketplace si pas de referer
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Si l'en-tête "Referer" est absent, la redirection
     * doit pointer vers /marketplace (valeur de repli).
     */
    public function testSetCurrencyRedirectsToDefaultWhenNoReferer(): void
    {
        // Arrange
        $controller = $this->buildController();

        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        // Pas d'en-tête Referer → valeur de repli = '/marketplace'

        // Act
        $response = $controller->setCurrency('TND', $request);

        // Assert
        $this->assertSame('/marketplace', $response->headers->get('Location'),
            'Sans referer, la redirection doit pointer vers /marketplace.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5 : TND est une devise valide et peut être sélectionnée
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : TND est la devise par défaut et doit être acceptée
     * comme n'importe quelle autre devise supportée.
     */
    public function testSetCurrencyAcceptsTndAsDefaultCurrency(): void
    {
        // Arrange
        $controller = $this->buildController();

        $session = new Session(new MockArraySessionStorage());
        $session->set('currency', 'EUR'); // on part d'EUR

        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace');

        // Act
        $controller->setCurrency('TND', $request);

        // Assert
        $this->assertSame('TND', $session->get('currency'),
            'TND doit pouvoir être sélectionnée et stockée en session.');
    }
}
