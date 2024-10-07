<?php

namespace App\Repository;

use App\Entity\UsersChats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UsersChats>
 */
class UsersChatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersChats::class);
    }

    public function findChatsByUserId($id) {
        $qb = $this->createQueryBuilder('uc1');

        $qb->select('uc1.chatId, uc2.userId')
            ->innerJoin('App\Entity\UsersChats', 'uc2', 'WITH', 'uc1.chatId = uc2.chatId')
            ->where('uc1.userId = :id')
            ->andWhere('uc2.userId != :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }

    public function findChatByUserIds($userId1, $userId2)
    {
        $qb = $this->createQueryBuilder('uc1')
            ->select('uc1.chatId')
            ->innerJoin('App\Entity\UsersChats', 'uc2', 'WITH', 'uc1.chatId = uc2.chatId')
            ->where('uc1.userId = :userId1')
            ->andWhere('uc2.userId = :userId2')
            ->setParameter('userId1', $userId1)
            ->setParameter('userId2', $userId2)
            ->setMaxResults(1)
            ->getQuery();

        return $qb->getOneOrNullResult() ?? null;
    }


//    /**
//     * @return UsersChats[] Returns an array of UsersChats objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UsersChats
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
