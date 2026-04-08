<?php

namespace App\Modules\Parcelle\Repository;

use App\Modules\Parcelle\Entity\Culture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CultureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Culture::class);
    }

    /**
     * Recherche une liste de cultures par nom, type ou état.
     *
     * @return Culture[]
     */
    public function findBySearchTerm(string $term): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->where($qb->expr()->like('LOWER(c.nom)', ':term'))
            ->orWhere($qb->expr()->like('LOWER(c.type_culture)', ':term'))
            ->orWhere($qb->expr()->like('LOWER(c.etat_culture)', ':term'))
            ->setParameter('term', '%'.mb_strtolower($term).'%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne toutes les cultures triées par date de récolte prévue la plus proche.
     *
     * @return Culture[]
     */
    public function findAllOrderByDateRecoltePrevueAsc(): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->addSelect('(CASE WHEN c.date_recolte_prevue IS NULL THEN 1 ELSE 0 END) AS HIDDEN hasDate')
            ->orderBy('hasDate', 'ASC')
            ->addOrderBy('c.date_recolte_prevue', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
