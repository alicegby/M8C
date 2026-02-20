<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(p.amountPaid)')
            ->where('p.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function getRevenueByMonth(): array
    {
        return $this->createQueryBuilder('p')
            ->select("TO_CHAR(p.purchasedAt, 'YYYY-MM') as month, SUM(p.amountPaid) as revenue, COUNT(p.id) as sales")
            ->where('p.status = :status')
            ->setParameter('status', 'completed')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getBestSellingMurderParties(): array
    {
        return $this->createQueryBuilder('p')
            ->select('mp.title, COUNT(p.id) as sales, SUM(p.amountPaid) as revenue')
            ->join('p.murderParty', 'mp')
            ->where('p.status = :status')
            ->setParameter('status', 'completed')
            ->groupBy('mp.id, mp.title')
            ->orderBy('sales', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}