<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function findByUserId($userId)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.user_id LIKE :userId');

        return $qb->getQuery()->getResult();
    }

    public function findByCourseId($courseId)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.course_id = :courseId');

        return $qb->getQuery()->getResult();
    }

    //Function returns all courses that $userId is enrolled for
    public function getCourses($userId)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->select('c.course_title, c.day, c.begin, c.end')
            ->join('App\Entity\Course', 'c', 'WITH', 'u.courseId = c.course_id')
            ->where('u.userId = :userId')
        ->setParameter(':userId', $userId);

        return $qb->getQuery()->getResult();
    }
}