<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }
    
    /**
     * @return Student[] Returns an array of Student objects matching search
     */
    public function search(string $search): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.firstName LIKE :search')
            ->orWhere('s.lastName LIKE :search')
            ->orWhere('s.cne LIKE :search')
            ->setParameter('search', '%'.$search.'%')
            ->orderBy('s.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
