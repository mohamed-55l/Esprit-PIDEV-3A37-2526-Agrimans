<?php

namespace App\Repository;

use App\Entity\AnimalHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnimalHistory>
 */
class AnimalHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnimalHistory::class);
    }

    /**
     * @return AnimalHistory[]
     */
    public function findRecentForUser(?int $userId, int $limit = 100): array
    {
        if ($userId === null) {
            return [];
        }

        return $this->createQueryBuilder('h')
            ->andWhere('h.userId = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('h.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
