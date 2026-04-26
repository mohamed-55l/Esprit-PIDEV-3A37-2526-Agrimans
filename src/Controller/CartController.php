<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CartItemRepository;
use App\Service\SessionCartService;
use App\Service\ExchangeRateService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace/cart')]
final class CartController extends AbstractController
{
    private const DEFAULT_BUYER_ID = 1;

    #[Route(name: 'app_marketplace_cart', methods: ['GET'])]
    public function index(SessionCartService $sessionCartService, \App\Repository\ProductRepository $productRepository, \App\Repository\ProductBundleRepository $bundleRepository): Response
    {
        $sessionItems = $sessionCartService->getCart();
        $enrichedItems = [];

        foreach ($sessionItems as $key => $item) {
            if (isset($item['isBundle']) && $item['isBundle']) {
                $bundle = $bundleRepository->find($item['id']);
                if ($bundle) {
                    $enrichedItems[] = [
                        'id' => $key,
                        'isBundle' => true,
                        'bundle' => $bundle,
                        'quantity' => $item['quantity'],
                        'lineTotal' => $item['price'] * $item['quantity']
                    ];
                }
            } else {
                $product = $productRepository->find($item['id']);
                if ($product) {
                    $enrichedItems[] = [
                        'id' => $key,
                        'isBundle' => false,
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'lineTotal' => $item['price'] * $item['quantity']
                    ];
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $enrichedItems,
            'total' => $sessionCartService->getCartTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_marketplace_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, Product $product, SessionCartService $sessionCartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        try {
            $sessionCartService->addToCart($product->getId(), $product->getPrice(), $product->getName(), $quantity);
            $this->addFlash('success', "{$quantity} kg de '{$product->getName()}' ajouté(s) au panier !");
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/remove/{id}', name: 'app_marketplace_cart_remove', methods: ['POST'])]
    public function removeItem(Request $request, string $id, SessionCartService $sessionCartService): Response
    {
        if ($this->isCsrfTokenValid('remove' . $id, $request->request->get('_token'))) {
            $sessionCartService->removeFromCart($id);
            $this->addFlash('success', 'Article retiré du panier.');
        }

        return $this->redirectToRoute('app_marketplace_cart');
    }

    #[Route('/clear', name: 'app_marketplace_cart_clear', methods: ['POST'])]
    public function clearCart(SessionCartService $sessionCartService): Response
    {
        $sessionCartService->clearCart();
        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('app_marketplace_cart');
    }

    #[Route('/checkout', name: 'app_marketplace_cart_checkout', methods: ['POST'])]
    public function checkout(SessionCartService $sessionCartService, ExchangeRateService $exchangeRateService): Response
    {
        $cart = $sessionCartService->getCart();
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_marketplace_cart');
        }

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $lineItems = [];
        foreach ($cart as $item) {
            // Convert price to EUR for Stripe
            $priceEur = $exchangeRateService->convert($item['price'], 'EUR');
            
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => (int) round($priceEur * 100), // Stripe expects cents for EUR
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_marketplace_cart_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_marketplace_cart_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/checkout/success', name: 'app_marketplace_cart_success', methods: ['GET'])]
    public function checkoutSuccess(SessionCartService $sessionCartService, \Doctrine\ORM\EntityManagerInterface $entityManager, \App\Repository\ProductRepository $productRepository, \App\Repository\ProductBundleRepository $bundleRepository): Response
    {
        $cart = $sessionCartService->getCart();
        
        foreach ($cart as $item) {
            if (isset($item['isBundle']) && $item['isBundle']) {
                $bundle = $bundleRepository->find($item['id']);
                if ($bundle) {
                    foreach ($bundle->getProducts() as $product) {
                        $newQty = max(0, $product->getQuantity() - $item['quantity']);
                        $product->setQuantity($newQty);
                    }
                }
            } else {
                $product = $productRepository->find($item['id']);
                if ($product) {
                    $newQty = max(0, $product->getQuantity() - $item['quantity']);
                    $product->setQuantity($newQty);
                }
            }
        }
        $entityManager->flush();

        $sessionCartService->clearCart();
        $this->addFlash('success', 'Paiement réussi ! Merci pour votre commande.');
        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/checkout/cancel', name: 'app_marketplace_cart_cancel', methods: ['GET'])]
    public function checkoutCancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
        return $this->redirectToRoute('app_marketplace_cart');
    }

    #[Route('/stats', name: 'app_marketplace_stats', methods: ['GET'])]
    public function stats(CartItemRepository $cartItemRepository): Response
    {
        return $this->render('cart/stats.html.twig', [
            'topProducts' => $cartItemRepository->getTopSellingProducts(5),
            'totalSales' => $cartItemRepository->getTotalSales(),
            'totalOrders' => $cartItemRepository->getTotalOrders(),
        ]);
    }
}
