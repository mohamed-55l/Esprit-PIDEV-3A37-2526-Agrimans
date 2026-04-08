<?php

namespace App\Modules\Animal\Repository;

use App\Modules\Animal\Entity\AnimalNourriture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnimalNourriture>
 */
class AnimalNourritureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnimalNourriture::class);
    }
}
