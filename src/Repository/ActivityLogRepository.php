<?php

namespace App\Repository;

use App\Entity\ActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 */
class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    // Add this method to get recent logs
    public function findRecentLogs(int $limit = 4): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.datetime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Add this to also get logs with user relation pre-loaded
    public function findRecentLogsWithUser(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.userId', 'u')
            ->addSelect('u')
            ->orderBy('l.datetime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return ActivityLog[] Returns an array of ActivityLog objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ActivityLog
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
