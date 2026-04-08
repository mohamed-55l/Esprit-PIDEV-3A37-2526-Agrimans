<?php

namespace App\Modules\Animal\Repository;

use App\Modules\Animal\Entity\Nourriture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nourriture>
 */
class NourritureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nourriture::class);
    }
}
