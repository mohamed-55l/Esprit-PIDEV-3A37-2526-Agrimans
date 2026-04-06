<?php

namespace App\Modules\Marketplace\Repository;

use App\Modules\Marketplace\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findBySellerId(int $sellerId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sellerId = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function search(string $query, ?string $category = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :query OR p.description LIKE :query OR p.supplier LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
