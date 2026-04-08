<?php
namespace App\Modules\Equipement\Repository;
use App\Modules\Equipement\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Recherche dans les commentaires et tri global
     */
    public function searchAndSort(?string $query, string $sortBy = 'dateReview', string $sortOrder = 'DESC')
    {
        $qb = $this->createQueryBuilder('r');

        if ($query) {
            $qb->andWhere('r.commentaire LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        $allowedSorts = ['dateReview', 'note'];
        if (in_array($sortBy, $allowedSorts)) {
            $qb->orderBy('r.' . $sortBy, $sortOrder === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('r.dateReview', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques globales sur les avis
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('r');
        
        $total = $qb->select('COUNT(r.id)')->getQuery()->getSingleScalarResult();
        
        $avg = $this->createQueryBuilder('r')
            ->select('AVG(r.note)')
            ->getQuery()
            ->getSingleScalarResult();

        $notesRaw = $this->createQueryBuilder('r')
            ->select('r.note, COUNT(r.id) as count')
            ->groupBy('r.note')
            ->orderBy('r.note', 'DESC')
            ->getQuery()
            ->getResult();

        $notesDistribution = [];
        foreach ($notesRaw as $row) {
            if ($row['note']) {
                $notesDistribution[$row['note']] = $row['count'];
            }
        }

        return [
            'total' => $total,
            'average' => $avg ? round($avg, 1) : 0,
            'distribution' => $notesDistribution
        ];
    }
}