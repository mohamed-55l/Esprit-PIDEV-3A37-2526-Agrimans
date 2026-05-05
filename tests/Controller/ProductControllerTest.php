<?php

namespace App\Tests\Controller;

use App\Controller\ProductController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for ProductController without needing a database.
 */
class ProductControllerTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Test 1: Currency switcher (setCurrency)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifies that the setCurrency method stores the requested currency in
     * the session and returns a RedirectResponse to the referrer.
     */
    public function testSetCurrencyStoresInSessionAndRedirects(): void
    {
        // Arrange
        $controller = new ProductController();
        // Since we are not extending AbstractController to mock the container easily,
        // we will manually construct the request and pass it.
        // The setCurrency method calls $this->redirect(), which requires the container.
        // So let's mock the container.
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $router = $this->createMock(\Symfony\Component\Routing\RouterInterface::class);
        
        $container->method('has')->willReturnCallback(function ($id) {
            return in_array($id, ['router', 'request_stack', 'twig']);
        });
        $container->method('get')->willReturnCallback(function ($id) use ($router) {
            if ($id === 'router') {
                return $router;
            }
            return null;
        });
        $controller->setContainer($container);

        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace?test=1');

        // Act
        $response = $controller->setCurrency('EUR', $request);

        // Assert
        $this->assertSame('EUR', $session->get('currency'), 'La devise EUR doit être enregistrée en session.');
        $this->assertSame(302, $response->getStatusCode(), 'Doit retourner une redirection (HTTP 302).');
        $this->assertSame('/marketplace?test=1', $response->headers->get('Location'), 'Doit rediriger vers le referer.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2: Currency switcher ignores invalid currencies
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifies that setCurrency ignores unsupported currencies and doesn't
     * update the session with an invalid value.
     */
    public function testSetCurrencyIgnoresInvalidCurrency(): void
    {
        // Arrange
        $controller = new ProductController();
        
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $router = $this->createMock(\Symfony\Component\Routing\RouterInterface::class);
        
        $container->method('has')->willReturnCallback(function ($id) {
            return in_array($id, ['router', 'request_stack', 'twig']);
        });
        $container->method('get')->willReturnCallback(function ($id) use ($router) {
            if ($id === 'router') {
                return $router;
            }
            return null;
        });
        $controller->setContainer($container);

        $session = new Session(new MockArraySessionStorage());
        $session->set('currency', 'TND'); // Default
        
        $request = new Request();
        $request->setSession($session);
        $request->headers->set('referer', '/marketplace');

        // Act
        $response = $controller->setCurrency('INVALID', $request);

        // Assert
        $this->assertSame('TND', $session->get('currency'), 'La devise invalide doit être ignorée.');
        $this->assertSame(302, $response->getStatusCode());
    }
}
