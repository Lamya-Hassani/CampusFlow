<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    //    /**
    //     * @return Schedule[] Returns an array of Schedule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Find schedule conflicts
     * @return Schedule[]
     */
    public function findConflicts(
        string $dayOfWeek,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        // ?object is a nullable object
        ?object $teacher,
        ?object $classe,
        string $room,
        ?int $excludeId = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->where('s.dayOfWeek = :day')
            ->andWhere('s.startTime < :endTime')
            ->andWhere('s.endTime > :startTime')
            ->setParameter('day', $dayOfWeek)
            ->setParameter('startTime', $startTime->format('H:i:s'))
            ->setParameter('endTime', $endTime->format('H:i:s'));

        if ($excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        $conflicts = [];

        // Check teacher conflict
        if ($teacher) {
            $teacherConflicts = clone $qb;
            $teacherConflicts->andWhere('s.teacher = :teacher')
                ->setParameter('teacher', $teacher);
            $conflicts = array_merge($conflicts, $teacherConflicts->getQuery()->getResult());
        }

        // Check class conflict
        if ($classe) {
            $classConflicts = clone $qb;
            $classConflicts->andWhere('s.classe = :classe')
                ->setParameter('classe', $classe);
            $conflicts = array_merge($conflicts, $classConflicts->getQuery()->getResult());
        }

        // Check room conflict
        $roomConflicts = clone $qb;
        $roomConflicts->andWhere('s.room = :room')
            ->setParameter('room', $room);
        $conflicts = array_merge($conflicts, $roomConflicts->getQuery()->getResult());

        return array_unique($conflicts, SORT_REGULAR);
    }
}
