<?php

// src/Repository/EnrolledRepository.php
namespace App\Repository;

use App\Entity\Enrolled;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrolled>
 *
 * @method Enrolled|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enrolled|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enrolled[]    findAll()
 * @method Enrolled[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnrolledRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrolled::class);
    }

    public function findByUserId(?string $user_id)
    {
        return $this->findBy(['user' => $user_id]);
    }
}
