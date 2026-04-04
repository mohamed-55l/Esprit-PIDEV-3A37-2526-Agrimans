<?php
namespace App\Modules\Equipement\Repository;
use App\Modules\Equipement\Entity\Equipement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class EquipementRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, Equipement::class); } }