<?php

namespace App\Modules\Parcelle\Repository;

use App\Modules\Parcelle\Entity\Parcelle;
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
}
