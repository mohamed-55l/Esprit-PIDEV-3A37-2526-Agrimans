<?php

namespace App\Modules\Marketplace\Service;

use App\Modules\Marketplace\Entity\Cart;
use App\Modules\Marketplace\Entity\CartItem;
use App\Modules\Marketplace\Entity\Product;
use App\Modules\Marketplace\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepository,
    ) {}

    public function getOrCreateCart(int $buyerId): Cart
    {
        return $this->cartRepository->findOrCreateByBuyerId($buyerId);
    }

    public function addToCart(int $buyerId, Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à 0.");
        }
        if ($product->getQuantity() < $quantity) {
            throw new \InvalidArgumentException("Stock insuffisant. Disponible: " . $product->getQuantity() . " kg.");
        }

        $cart = $this->getOrCreateCart($buyerId);

        // Check if product already in cart
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $newQty = $item->getQuantity() + $quantity;
                if ($product->getQuantity() < $newQty) {
                    throw new \InvalidArgumentException("Stock insuffisant pour cette quantité totale.");
                }
                $item->setQuantity($newQty);
                $this->em->flush();
                return;
            }
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);

        $this->em->persist($cartItem);
        $this->em->flush();
    }

    public function removeFromCart(CartItem $cartItem): void
    {
        $this->em->remove($cartItem);
        $this->em->flush();
    }

    public function clearCart(int $buyerId): void
    {
        $cart = $this->cartRepository->findOneBy(['buyerId' => $buyerId]);
        if ($cart) {
            foreach ($cart->getItems() as $item) {
                $this->em->remove($item);
            }
            $this->em->flush();
        }
    }

    public function checkout(int $buyerId): array
    {
        $cart = $this->getOrCreateCart($buyerId);
        $items = $cart->getItems();

        if ($items->isEmpty()) {
            throw new \InvalidArgumentException("Le panier est vide.");
        }

        // Validate stock for all items
        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product->getQuantity() < $item->getQuantity()) {
                throw new \InvalidArgumentException(
                    "Stock insuffisant pour '{$product->getName()}'. Disponible: {$product->getQuantity()} kg."
                );
            }
        }

        // Update stock
        $total = 0;
        $orderItems = [];
        foreach ($items as $item) {
            $product = $item->getProduct();
            $product->setQuantity($product->getQuantity() - $item->getQuantity());
            $lineTotal = $item->getLineTotal();
            $total += $lineTotal;
            $orderItems[] = [
                'product' => $product->getName(),
                'quantity' => $item->getQuantity(),
                'unitPrice' => $product->getPrice(),
                'lineTotal' => $lineTotal,
            ];
        }

        // Clear cart after checkout
        foreach ($items->toArray() as $item) {
            $this->em->remove($item);
        }

        $this->em->flush();

        return [
            'items' => $orderItems,
            'total' => $total,
        ];
    }

    public function getCartItemCount(int $buyerId): int
    {
        $cart = $this->cartRepository->findOneBy(['buyerId' => $buyerId]);
        if (!$cart) {
            return 0;
        }
        return $cart->getItems()->count();
    }
}
