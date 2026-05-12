<?php

namespace App\Repository;

use App\Entity\UserNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserNotification>
 */
class UserNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotification::class);
    }

    public function countUnreadForUser(int $userId, string $context = 'animal'): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.userId = :uid')
            ->andWhere('n.context = :ctx')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('uid', $userId)
            ->setParameter('ctx', $context)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return UserNotification[]
     */
    public function findForUser(int $userId, string $context = 'animal', int $limit = 50): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.userId = :uid')
            ->andWhere('n.context = :ctx')
            ->setParameter('uid', $userId)
            ->setParameter('ctx', $context)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findMaxIdForUser(int $userId, string $context = 'animal'): int
    {
        $v = $this->createQueryBuilder('n')
            ->select('MAX(n.id)')
            ->andWhere('n.userId = :uid')
            ->andWhere('n.context = :ctx')
            ->setParameter('uid', $userId)
            ->setParameter('ctx', $context)
            ->getQuery()
            ->getSingleScalarResult();

        return $v !== null ? (int) $v : 0;
    }

    /**
     * @return UserNotification[]
     */
    public function findAnimalNotificationsWithIdGreaterThan(int $userId, int $afterId, string $context = 'animal', int $limit = 25): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.userId = :uid')
            ->andWhere('n.context = :ctx')
            ->andWhere('n.id > :after')
            ->setParameter('uid', $userId)
            ->setParameter('ctx', $context)
            ->setParameter('after', $afterId)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function markAllReadForUser(int $userId, string $context = 'animal'): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->update(UserNotification::class, 'n')
            ->set('n.readAt', ':now')
            ->andWhere('n.userId = :uid')
            ->andWhere('n.context = :ctx')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('uid', $userId)
            ->setParameter('ctx', $context)
            ->getQuery()
            ->execute();
    }
}
