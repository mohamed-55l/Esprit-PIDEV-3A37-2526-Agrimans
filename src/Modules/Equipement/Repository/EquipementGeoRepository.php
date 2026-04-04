<?php
namespace App\Modules\Equipement\Repository;
use App\Modules\Equipement\Entity\EquipementGeo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class EquipementGeoRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, EquipementGeo::class); } }