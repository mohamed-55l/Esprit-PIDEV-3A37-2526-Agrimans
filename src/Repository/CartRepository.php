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
        $cart = $this->findOneBy(['buyerId' => $buyerId]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setBuyerId($buyerId);
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }

        return $cart;
    }
}
