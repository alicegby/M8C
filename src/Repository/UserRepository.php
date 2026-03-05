<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findBySupabaseId(string $supabaseId): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.supabaseId = :id')
            ->setParameter('id', $supabaseId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByBirthday(int $day, int $month): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT id FROM users
            WHERE dob IS NOT NULL
            AND is_deleted = false
            AND EXTRACT(DAY FROM dob) = :day
            AND EXTRACT(MONTH FROM dob) = :month
        ';

        $result = $conn->executeQuery($sql, ['day' => $day, 'month' => $month]);
        $ids = $result->fetchAllAssociative();

        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', array_column($ids, 'id'))
            ->getQuery()
            ->getResult();
    }
}