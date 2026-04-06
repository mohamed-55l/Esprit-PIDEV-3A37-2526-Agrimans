<?php

namespace App\Modules\Marketplace\Repository;

use App\Modules\Marketplace\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    /**
     * @return array{productId: int, productName: string, totalQuantitySold: int, totalRevenue: float}[]
     */
    public function getTopSellingProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('ci')
            ->select('IDENTITY(ci.product) as productId, p.name as productName, SUM(ci.quantity) as totalQuantitySold, SUM(ci.quantity * p.price) as totalRevenue')
            ->join('ci.product', 'p')
            ->groupBy('ci.product')
            ->orderBy('totalQuantitySold', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalSales(): float
    {
        $result = $this->createQueryBuilder('ci')
            ->select('SUM(ci.quantity * p.price) as totalSales')
            ->join('ci.product', 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getTotalOrders(): int
    {
        $result = $this->createQueryBuilder('ci')
            ->select('COUNT(ci.id) as totalOrders')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
