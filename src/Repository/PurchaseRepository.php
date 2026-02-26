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

    // CA et ventes par MP
    public function getSalesByMP(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('mp.id as murderPartyId, mp.title as title, COUNT(p.id) as totalSales, SUM(p.amountPaid) as totalRevenue')
            ->join('p.murderParty', 'mp')
            ->where('p.status = :status')
            ->setParameter('status', 'completed');

        if (!empty($mpIds)) {
            $qb->andWhere('mp.id IN (:mpIds)')->setParameter('mpIds', $mpIds);
        }
        if ($start) {
            $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        }
        if ($end) {
            $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);
        }

        $results = $qb->groupBy('mp.id, mp.title')->orderBy('totalRevenue', 'DESC')->getQuery()->getResult();

        return array_map(fn($r) => [
            'murderPartyId' => $r['murderPartyId'],
            'title' => $r['title'],
            'totalSales' => (int) $r['totalSales'],
            'totalRevenue' => round((float) $r['totalRevenue'], 2),
        ], $results);
    }

    // Promo vs plein pot
    public function getPromoVsFullPrice(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        function buildPromoQb($repo, bool $withPromo, ?\DateTime $start, ?\DateTime $end) {
            $qb = $repo->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->where('p.status = :status')
                ->setParameter('status', 'completed');

            $withPromo
                ? $qb->andWhere('p.promoCode IS NOT NULL')
                : $qb->andWhere('p.promoCode IS NULL');

            if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
            if ($end) $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

            return (int) $qb->getQuery()->getSingleScalarResult();
        }

        return [
            'withPromo' => buildPromoQb($this, true, $start, $end),
            'withoutPromo' => buildPromoQb($this, false, $start, $end),
        ];
    }

    // Panier moyen par MP
    public function getAverageBasket(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('mp.id as murderPartyId, mp.title as title, AVG(p.amountPaid) as avgAmount')
            ->join('p.murderParty', 'mp')
            ->where('p.status = :status')
            ->setParameter('status', 'completed');

        if (!empty($mpIds)) {
            $qb->andWhere('mp.id IN (:mpIds)')->setParameter('mpIds', $mpIds);
        }
        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end) $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        $results = $qb->groupBy('mp.id, mp.title')->orderBy('avgAmount', 'DESC')->getQuery()->getResult();

        return array_map(fn($r) => [
            'murderPartyId' => $r['murderPartyId'],
            'title' => $r['title'],
            'avgAmount' => round((float) $r['avgAmount'], 2),
        ], $results);
    }

    // Répartition méthodes de paiement
    public function getPaymentMethodDistribution(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.paymentMethod as method, COUNT(p.id) as count')
            ->where('p.status = :status')
            ->setParameter('status', 'completed');

        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end) $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        $results = $qb->groupBy('p.paymentMethod')->orderBy('count', 'DESC')->getQuery()->getResult();

        return array_map(fn($r) => [
            'method' => $r['method'],
            'count' => (int) $r['count'],
        ], $results);
    }

    // Taux de retour joueurs
    public function getReturningPlayersRate(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $where = 'WHERE status = :status';
        $params = ['status' => 'completed'];
        $types = [];

        if ($start) {
            $where .= ' AND purchased_at >= :start';
            $params['start'] = $start->format('Y-m-d');
        }
        if ($end) {
            $where .= ' AND purchased_at <= :end';
            $params['end'] = $end->format('Y-m-d');
        }

        $sql = "
            SELECT
                COUNT(CASE WHEN purchase_count >= 2 THEN 1 END) as returning,
                COUNT(CASE WHEN purchase_count = 1 THEN 1 END) as unique_players
            FROM (
                SELECT user_id, COUNT(id) as purchase_count
                FROM purchases
                {$where}
                GROUP BY user_id
            ) sub
        ";

        $result = $conn->executeQuery($sql, $params)->fetchAssociative();

        return [
            'returning' => (int) ($result['returning'] ?? 0),
            'unique' => (int) ($result['unique_players'] ?? 0),
        ];
    }
}