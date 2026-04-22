<?php
namespace App\Repository;
use App\Entity\Equipement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class EquipementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipement::class);
    }

    /**
     * Recherche par nom ou type, et tri global
     */
    public function searchAndSort(?string $query, string $sortBy = 'nom', string $sortOrder = 'ASC')
    {
        $qb = $this->createQueryBuilder('e');

        if ($query) {
            $qb->andWhere('e.nom LIKE :query OR e.type LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        $allowedSorts = ['nom', 'prix', 'type', 'disponibilite'];
        if (in_array($sortBy, $allowedSorts)) {
            $qb->orderBy('e.' . $sortBy, $sortOrder === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('e.nom', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques globales sur les équipements
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('e');
        
        $total = $qb->select('COUNT(e.id) as total')->getQuery()->getSingleScalarResult();
        
        $dispos = $this->createQueryBuilder('e')
            ->select('e.disponibilite, COUNT(e.id) as count')
            ->groupBy('e.disponibilite')
            ->getQuery()
            ->getResult();
            
        $repartition = [];
        foreach ($dispos as $d) {
            $key = $d['disponibilite'] ?: 'Non défini';
            $repartition[$key] = $d['count'];
        }

        return [
            'total' => $total,
            'repartition' => $repartition
        ];
    }

    /**
     * Récupère les équipements assignés à un utilisateur spécifique
     */
    public function findByUser(\App\Entity\Users $user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les équipements en chargeant l’utilisateur associé par LEFT JOIN.
     * Cela évite les erreurs de proxy Doctrine quand un user_id pointe vers
     * un utilisateur supprimé (retourne null au lieu de lever une exception).
     */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->addSelect('u')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}