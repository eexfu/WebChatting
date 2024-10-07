<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByName($name)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.firstname LIKE :name')
            ->orWhere('u.lastname LIKE :name')
            ->orWhere($qb->expr()->concat('u.firstname', $qb->expr()->literal(' '), 'u.lastname') . ' LIKE :name')
            ->setParameter('name', '%' . $name . '%');

        return $qb->getQuery()->getResult();
    }

    public function findById($id){
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }
}
