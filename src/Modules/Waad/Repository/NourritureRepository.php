<?php

namespace App\Modules\Waad\Repository;

use App\Modules\Waad\Entity\Nourriture;
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
