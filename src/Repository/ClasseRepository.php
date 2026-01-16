<?php

namespace App\Repository;

use App\Entity\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Classe>
 */
class ClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classe::class);
    }

    public function findByNameOrField(string $query): array
    {
        return $this->createQueryBuilder('c') // c is the alias for the Classe entity
            ->where('c.name LIKE :q') // :q is the parameter for the search query
            ->orWhere('c.field LIKE :q')
            ->orWhere('c.level LIKE :q')
            ->setParameter('q', '%' . $query . '%') // here we set the parameter for the search query
            ->getQuery() // here we get the query that is the result of the search
            ->getResult();
    }

    /**
     * @return array Returns an array with class name and student count
     */
    public function getStudentsCountByClass(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.name', 'COUNT(s.id) as studentCount')  // here we select the class name and the count of students
            ->leftJoin('c.students', 's') // here we join the students table to the classes table
            ->groupBy('c.id') // here we group the results by class id
            ->getQuery() // here we get the query that is the result of the search
            ->getResult();
    }
}
