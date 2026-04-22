<?php

namespace App\Repository;

use App\Entity\ProductBundle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductBundle>
 */
class ProductBundleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductBundle::class);
    }

    /**
     * Find all active bundles
     */
    public function findActiveBundle()
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find bundles by discount percentage
     */
    public function findByMinDiscount(float $minDiscount)
    {
        return $this->createQueryBuilder('b')
            ->where('b.discountPercentage >= :discount')
            ->andWhere('b.isActive = :active')
            ->setParameter('discount', $minDiscount)
            ->setParameter('active', true)
            ->orderBy('b.discountPercentage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Paginate active bundles
     */
    public function findActiveBundlesPaginated(int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count active bundles
     */
    public function countActiveBundles(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
