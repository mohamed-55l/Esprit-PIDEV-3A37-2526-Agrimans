<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionCartService
{
    private const CART_SESSION_KEY = 'shopping_cart';

    public function __construct(private RequestStack $requestStack) {}

    /**
     * Get cart from session
     */
    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::CART_SESSION_KEY, []);
    }

    /**
     * Add item to session cart
     */
    public function addToCart(int $productId, float $price, string $productName, int $quantity = 1, bool $isBundle = false): void
    {
        $cart = $this->getCart();
        $key = ($isBundle ? 'bundle_' : 'product_') . $productId;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $price,
                'quantity' => $quantity,
                'isBundle' => $isBundle,
                'addedAt' => new \DateTime(),
            ];
        }

        $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Remove item from session cart
     */
    public function removeFromCart(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Update quantity of item
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                $this->removeFromCart($productId);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
            }
        }
    }

    /**
     * Get cart count (number of items)
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return count($cart);
    }

    /**
     * Get cart total price
     */
    public function getCartTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['price'] * $item['quantity']);
        }
        return round($total, 2);
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        $this->requestStack->getSession()->remove(self::CART_SESSION_KEY);
    }

    /**
     * Check if item is in cart
     */
    public function hasItem(int $productId): bool
    {
        $cart = $this->getCart();
        return isset($cart[$productId]);
    }

    /**
     * Get item from cart
     */
    public function getItem(int $productId): ?array
    {
        $cart = $this->getCart();
        return $cart[$productId] ?? null;
    }

    /**
     * Add bundle to session cart
     */
    public function addBundleToCart(int $bundleId, string $bundleName, float $bundlePrice, array $productIds): void
    {
        $cart = $this->getCart();
        $bundleKey = "bundle_$bundleId";

        $cart[$bundleKey] = [
            'id' => $bundleId,
            'name' => $bundleName,
            'price' => $bundlePrice,
            'quantity' => 1,
            'isBundle' => true,
            'products' => $productIds,
            'addedAt' => new \DateTime(),
        ];

        $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Get all items count including bundles
     */
    public function getItemsCount(): int
    {
        $cart = $this->getCart();
        return count($cart);
    }
}
