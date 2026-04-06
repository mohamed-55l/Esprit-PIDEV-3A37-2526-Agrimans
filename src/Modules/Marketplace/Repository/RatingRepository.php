<?php

namespace App\Modules\Marketplace\Repository;

use App\Modules\Marketplace\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function getAverageRating(int $productId): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.product = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) ($result ?? 0), 1);
    }

    /**
     * @return array{HIGH: int, GOOD: int, LOW: int}
     */
    public function getPriceCategoryAnalysis(int $productId): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.priceCategory, COUNT(r.id) as cnt')
            ->andWhere('r.product = :productId')
            ->andWhere('r.priceCategory IS NOT NULL')
            ->setParameter('productId', $productId)
            ->groupBy('r.priceCategory')
            ->getQuery()
            ->getResult();

        $analysis = ['HIGH' => 0, 'GOOD' => 0, 'LOW' => 0];
        foreach ($results as $row) {
            $analysis[$row['priceCategory']] = (int) $row['cnt'];
        }

        return $analysis;
    }
}
