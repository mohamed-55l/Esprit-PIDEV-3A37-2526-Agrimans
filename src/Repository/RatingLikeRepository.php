<?php

namespace App\Repository;

use App\Entity\RatingLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingLike::class);
    }

    public function findUserVote(int $ratingId, int $userId): ?RatingLike
    {
        return $this->createQueryBuilder('rl')
            ->where('rl.rating = :ratingId')
            ->andWhere('rl.user = :userId')
            ->setParameter('ratingId', $ratingId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
