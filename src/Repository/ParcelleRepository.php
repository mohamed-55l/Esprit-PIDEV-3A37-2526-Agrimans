<?php

namespace App\Repository;

use App\Entity\Parcelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParcelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcelle::class);
    }

    /**
     * Recherche une liste de parcelles par nom, localisation ou type de sol.
     *
     * @return Parcelle[]
     */
    public function findBySearchTerm(string $term): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where($qb->expr()->like('LOWER(p.nom)', ':term'))
            ->orWhere($qb->expr()->like('LOWER(p.localisation)', ':term'))
            ->orWhere($qb->expr()->like('LOWER(p.type_sol)', ':term'))
            ->setParameter('term', '%'.mb_strtolower($term).'%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne toutes les parcelles triées par superficie décroissante.
     *
     * @return Parcelle[]
     */
    public function findAllOrderBySuperficieDesc(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.superficie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les parcelles d'un utilisateur spécifique, triées par superficie.
     *
     * @return Parcelle[]
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.superficie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche dans les parcelles d'un utilisateur spécifique.
     *
     * @return Parcelle[]
     */
    public function findBySearchTermAndUser(string $term, $user): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where('p.user = :user')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(p.nom)', ':term'),
                    $qb->expr()->like('LOWER(p.localisation)', ':term'),
                    $qb->expr()->like('LOWER(p.type_sol)', ':term')
                )
            )
            ->setParameter('user', $user)
            ->setParameter('term', '%' . mb_strtolower($term) . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
