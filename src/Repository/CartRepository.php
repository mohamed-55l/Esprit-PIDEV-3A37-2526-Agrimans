<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findOrCreateByBuyerId(int $buyerId): Cart
    {
        $cart = $this->findOneBy(['user' => $buyerId]);

        if (!$cart) {
            $cart = new Cart();
            // Assign a user reference without requiring a full fetch
            $userReference = $this->getEntityManager()->getReference(\App\Entity\User::class, $buyerId);
            $cart->setUser($userReference);
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }

        return $cart;
    }
}
