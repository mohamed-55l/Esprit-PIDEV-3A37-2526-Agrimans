<?php
namespace App\Modules\Equipement\Repository;
use App\Modules\Equipement\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ReviewRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, Review::class); } }