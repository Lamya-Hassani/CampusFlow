<?php

namespace App\Repository;

use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Teacher>
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    public function findByNameOrEmail(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.user', 'u')
            ->where('t.firstName LIKE :q')
            ->orWhere('t.lastName LIKE :q')
            ->orWhere('u.email LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}
