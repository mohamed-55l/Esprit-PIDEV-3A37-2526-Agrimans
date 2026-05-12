<?php

namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    public function createActiveQueryBuilder(?int $forUserId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.deletedAt IS NULL');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return $qb->orderBy('a.id', 'DESC');
    }

    public function findOneActiveById(int $id, ?int $forUserId = null): ?Animal
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.deletedAt IS NULL')
            ->setParameter('id', $id);

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneArchivedById(int $id, ?int $forUserId = null): ?Animal
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.deletedAt IS NOT NULL')
            ->setParameter('id', $id);

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Animal[]
     */
    public function findAllArchived(?int $forUserId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.deletedAt IS NOT NULL')
            ->orderBy('a.deletedAt', 'DESC');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<string, int>
     */
    public function countActiveGroupedByHealth(?int $forUserId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.etatSante AS h', 'COUNT(a.id) AS c')
            ->andWhere('a.deletedAt IS NULL')
            ->groupBy('a.etatSante');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        $rows = $qb->getQuery()->getResult();
        $out = [];
        foreach ($rows as $row) {
            $label = $row['h'] ?? 'Non renseigné';
            $out[(string) $label] = (int) $row['c'];
        }

        return $out;
    }

    /**
     * @return array<string, int>
     */
    public function countActiveGroupedBySpecies(?int $forUserId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.espece AS s', 'COUNT(a.id) AS c')
            ->andWhere('a.deletedAt IS NULL')
            ->groupBy('a.espece');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        $rows = $qb->getQuery()->getResult();
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['s']] = (int) $row['c'];
        }

        return $out;
    }

    public function countActive(?int $forUserId = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.deletedAt IS NULL');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countArchived(?int $forUserId = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.deletedAt IS NOT NULL');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function countActiveGroupedByBreed(?int $forUserId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.race AS r', 'COUNT(a.id) AS c')
            ->andWhere('a.deletedAt IS NULL')
            ->groupBy('a.race');

        if ($forUserId !== null) {
            $qb->andWhere('a.userId IS NULL OR a.userId = :uid')
                ->setParameter('uid', $forUserId);
        }

        $rows = $qb->getQuery()->getResult();
        $out = [];
        foreach ($rows as $row) {
            $label = $row['r'] ?? '';
            $label = trim((string) $label);
            if ($label === '') {
                $label = 'Non renseigné';
            }
            $out[$label] = (int) $row['c'];
        }

        return $out;
    }
}
