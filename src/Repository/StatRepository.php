<?php

namespace App\Repository;

use App\Entity\Purchase;
use App\Entity\GameResult;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class StatRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    // ============================================================
    // STATS SQL (Supabase / PostgreSQL via Doctrine ORM)
    // ============================================================

    public function getTotalRevenue(?\DateTime $start = null, ?\DateTime $end = null): float
    {
        $qb = $this->em->createQueryBuilder()
            ->select('SUM(p.amountPaid)')
            ->from(Purchase::class, 'p')
            ->where("p.status = 'completed'");

        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        return (float) ($qb->getQuery()->getSingleScalarResult() ?? 0);
    }

    public function getSalesByMP(?array $mpIds, ?\DateTime $start, ?\DateTime $end): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('mp.id as murderPartyId, mp.title, COUNT(p.id) as totalSales, SUM(p.amountPaid) as totalRevenue')
            ->from(Purchase::class, 'p')
            ->join('p.murderParty', 'mp')
            ->where("p.status = 'completed'")
            ->groupBy('mp.id, mp.title');

        if (!empty($mpIds)) $qb->andWhere('mp.id IN (:mpIds)')->setParameter('mpIds', $mpIds);
        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        return array_map(fn($r) => [
            'murderPartyId' => $r['murderPartyId'],
            'title'         => $r['title'],
            'totalSales'    => (int) $r['totalSales'],
            'totalRevenue'  => round((float) $r['totalRevenue'], 2),
        ], $qb->getQuery()->getArrayResult());
    }

    public function getPromoVsFullPrice(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $base = $this->em->createQueryBuilder()->from(Purchase::class, 'p')->where("p.status = 'completed'");

        $withPromo    = (clone $base)->select('COUNT(p.id)')->andWhere('p.promoCode IS NOT NULL');
        $withoutPromo = (clone $base)->select('COUNT(p.id)')->andWhere('p.promoCode IS NULL');

        foreach ([$withPromo, $withoutPromo] as $qb) {
            if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
            if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);
        }

        return [
            'withPromo'    => (int) $withPromo->getQuery()->getSingleScalarResult(),
            'withoutPromo' => (int) $withoutPromo->getQuery()->getSingleScalarResult(),
        ];
    }

    public function getAverageBasket(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('mp.id as murderPartyId, mp.title, AVG(p.amountPaid) as avgAmount')
            ->from(Purchase::class, 'p')
            ->join('p.murderParty', 'mp')
            ->where("p.status = 'completed'")
            ->groupBy('mp.id, mp.title')
            ->orderBy('avgAmount', 'DESC');

        if (!empty($mpIds)) $qb->andWhere('mp.id IN (:mpIds)')->setParameter('mpIds', $mpIds);
        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        return array_map(fn($r) => [
            'murderPartyId' => $r['murderPartyId'],
            'title'         => $r['title'],
            'avgAmount'     => round((float) $r['avgAmount'], 2),
        ], $qb->getQuery()->getArrayResult());
    }

    public function getPaymentMethodDistribution(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p.paymentMethod as method, COUNT(p.id) as count')
            ->from(Purchase::class, 'p')
            ->where("p.status = 'completed'")
            ->groupBy('p.paymentMethod');

        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        return array_map(fn($r) => [
            'method' => $r['method'] ?? 'Inconnu',
            'count'  => (int) $r['count'],
        ], $qb->getQuery()->getArrayResult());
    }

    public function getReturningPlayersRate(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('IDENTITY(p.user) as userId, COUNT(p.id) as purchaseCount')
            ->from(Purchase::class, 'p')
            ->where("p.status = 'completed'")
            ->groupBy('p.user');

        if ($start) $qb->andWhere('p.purchasedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('p.purchasedAt <= :end')->setParameter('end', $end);

        $rows = $qb->getQuery()->getArrayResult();

        return [
            'returning' => count(array_filter($rows, fn($r) => (int)$r['purchaseCount'] >= 2)),
            'unique'    => count(array_filter($rows, fn($r) => (int)$r['purchaseCount'] === 1)),
        ];
    }

    public function getSuccessRateByMurderParty(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('mp.id as murderPartyId, mp.title,
                      COUNT(gr.id) as totalSessions,
                      SUM(CASE WHEN gr.success = true THEN 1 ELSE 0 END) as wonSessions,
                      SUM(gr.totalVotesCount) as totalVotes,
                      SUM(gr.correctVotesCount) as correctVotes')
            ->from(GameResult::class, 'gr')
            ->join('gr.gameSession', 'gs')
            ->join('gs.murderParty', 'mp')
            ->groupBy('mp.id, mp.title');

        if (!empty($mpIds)) $qb->andWhere('mp.id IN (:mpIds)')->setParameter('mpIds', $mpIds);
        if ($start) $qb->andWhere('gr.completedAt >= :start')->setParameter('start', $start);
        if ($end)   $qb->andWhere('gr.completedAt <= :end')->setParameter('end', $end);

        return array_map(function($r) {
            $total   = (int) $r['totalSessions'];
            $won     = (int) $r['wonSessions'];
            $votes   = (int) $r['totalVotes'];
            $correct = (int) $r['correctVotes'];
            return [
                'murderPartyId'         => $r['murderPartyId'],
                'title'                 => $r['title'],
                'totalSessions'         => $total,
                'wonSessions'           => $won,
                'sessionSuccessPercent' => $total > 0 ? round($won * 100 / $total, 1) : 0,
                'totalVotes'            => $votes,
                'correctVotes'          => $correct,
                'playerSuccessPercent'  => $votes > 0 ? round($correct * 100 / $votes, 1) : 0,
            ];
        }, $qb->getQuery()->getArrayResult());
    }

    public function getSourceDistribution(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        return [];
    }

    // ============================================================
    // MongoDB — désactivé temporairement (SIGSEGV driver)
    // Retourne des données mock pour ne pas crasher FPM
    // ============================================================
    public function getRegistrationsByPeriod(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        return [];
    }
}