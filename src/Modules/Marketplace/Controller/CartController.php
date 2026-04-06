<?php

namespace App\Modules\Marketplace\Controller;

use App\Modules\Marketplace\Entity\CartItem;
use App\Modules\Marketplace\Entity\Product;
use App\Modules\Marketplace\Repository\CartItemRepository;
use App\Modules\Marketplace\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace/cart')]
final class CartController extends AbstractController
{
    private const DEFAULT_BUYER_ID = 1;

    #[Route(name: 'app_marketplace_cart', methods: ['GET'])]
    public function index(CartService $cartService): Response
    {
        $cart = $cartService->getOrCreateCart(self::DEFAULT_BUYER_ID);

        return $this->render('Marketplace/cart/index.html.twig', [
            'cart' => $cart,
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_marketplace_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, Product $product, CartService $cartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        try {
            $cartService->addToCart(self::DEFAULT_BUYER_ID, $product, $quantity);
            $this->addFlash('success', "{$quantity} kg de '{$product->getName()}' ajouté(s) au panier !");
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/remove/{id}', name: 'app_marketplace_cart_remove', methods: ['POST'])]
    public function removeItem(Request $request, CartItem $cartItem, CartService $cartService): Response
    {
        if ($this->isCsrfTokenValid('remove' . $cartItem->getId(), $request->request->get('_token'))) {
            $cartService->removeFromCart($cartItem);
            $this->addFlash('success', 'Article retiré du panier.');
        }

        return $this->redirectToRoute('app_marketplace_cart');
    }

    #[Route('/clear', name: 'app_marketplace_cart_clear', methods: ['POST'])]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clearCart(self::DEFAULT_BUYER_ID);
        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('app_marketplace_cart');
    }

    #[Route('/checkout', name: 'app_marketplace_cart_checkout', methods: ['POST'])]
    public function checkout(CartService $cartService): Response
    {
        try {
            $result = $cartService->checkout(self::DEFAULT_BUYER_ID);

            $this->addFlash('success', sprintf(
                'Commande validée ! Total: %.2f DT (%d article(s)).',
                $result['total'],
                count($result['items'])
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_marketplace_cart');
        }

        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/stats', name: 'app_marketplace_stats', methods: ['GET'])]
    public function stats(CartItemRepository $cartItemRepository): Response
    {
        return $this->render('Marketplace/cart/stats.html.twig', [
            'topProducts' => $cartItemRepository->getTopSellingProducts(5),
            'totalSales' => $cartItemRepository->getTotalSales(),
            'totalOrders' => $cartItemRepository->getTotalOrders(),
        ]);
    }
}
