<?php

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Grade>
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    /**
     * Retourne le taux de réussite (notes >= 10) et le volume total de notes.
     *
     * @return array{successRate: float, total: int}
     */
    public function getSuccessRate(): array
    {
        $result = $this->createQueryBuilder('g')
            ->select('COUNT(g.id) AS total', 'SUM(CASE WHEN g.value >= 10 THEN 1 ELSE 0 END) AS success')
            ->getQuery()
            ->getSingleResult();

        $total = (int) ($result['total'] ?? 0);
        $success = (int) ($result['success'] ?? 0);
        $rate = $total > 0 ? round(($success / $total) * 100, 2) : 0.0;

        return [
            'successRate' => $rate,
            'total' => $total,
        ];
    }

    /**
     * Moyenne mensuelle des notes pour l'année en cours.
     *
     * @return array<int, float> Clé = numéro du mois, valeur = moyenne.
     */
    public function getMonthlyAverages(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                MONTH(g.created_at) AS month,
                AVG(g.value) AS avgGrade
            FROM grade g
            WHERE YEAR(g.created_at) = :year
            GROUP BY month
            ORDER BY month ASC
        ';

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'year' => (int) date('Y'),
        ])->fetchAllAssociative();

        $data = [];
        foreach ($results as $row) {
            $data[(int) $row['month']] = round((float) $row['avgGrade'], 2);
        }

        return $data;
    }


    //    /**
    //     * @return Grade[] Returns an array of Grade objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Grade
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
