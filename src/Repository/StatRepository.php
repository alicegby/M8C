<?php

namespace App\Repository;

use App\Document\GameStat;
use App\Document\PurchaseStat;
use App\Document\UserRegistrationStat;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\BSON\UTCDateTime;

class StatRepository
{
    public function __construct(private DocumentManager $dm) {}

    private function mongoDate(\DateTime $dt): UTCDateTime
    {
        return new UTCDateTime($dt->getTimestamp() * 1000);
    }

    private function buildDateMatch(?\DateTime $start, ?\DateTime $end, string $field = 'purchasedAt'): array
    {
        $match = [];
        if ($start) $match[$field]['$gte'] = $this->mongoDate($start);
        if ($end)   $match[$field]['$lte'] = $this->mongoDate($end);
        return $match;
    }

    // CA et ventes par MP
    public function getSalesByMP(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = array_merge(
            !empty($mpIds) ? ['murderPartyId' => ['$in' => $mpIds]] : [],
            $this->buildDateMatch($start, $end)
        );

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id' => ['murderPartyId' => '$murderPartyId', 'title' => '$murderPartyTitle'],
                'totalSales'   => ['$sum' => 1],
                'totalRevenue' => ['$sum' => '$amountPaid'],
            ]],
            ['$sort' => ['totalRevenue' => -1]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(fn($r) => [
            'murderPartyId' => $r['_id']['murderPartyId'] ?? null,
            'title'         => $r['_id']['title'] ?? 'Inconnu',
            'totalSales'    => (int) $r['totalSales'],
            'totalRevenue'  => round((float) $r['totalRevenue'], 2),
        ], $results);
    }

    // Promo vs plein pot
    public function getPromoVsFullPrice(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $dateMatch = $this->buildDateMatch($start, $end);

        $withPromo = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->countDocuments(array_merge($dateMatch, ['promoCode' => ['$ne' => null]]));

        $withoutPromo = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->countDocuments(array_merge($dateMatch, ['promoCode' => null]));

        return [
            'withPromo'    => (int) $withPromo,
            'withoutPromo' => (int) $withoutPromo,
        ];
    }

    // Panier moyen par MP
    public function getAverageBasket(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = array_merge(
            !empty($mpIds) ? ['murderPartyId' => ['$in' => $mpIds]] : [],
            $this->buildDateMatch($start, $end)
        );

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'       => ['murderPartyId' => '$murderPartyId', 'title' => '$murderPartyTitle'],
                'avgAmount' => ['$avg' => '$amountPaid'],
            ]],
            ['$sort' => ['avgAmount' => -1]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(fn($r) => [
            'murderPartyId' => $r['_id']['murderPartyId'] ?? null,
            'title'         => $r['_id']['title'] ?? 'Inconnu',
            'avgAmount'     => round((float) $r['avgAmount'], 2),
        ], $results);
    }

    // Répartition méthodes de paiement
    public function getPaymentMethodDistribution(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = $this->buildDateMatch($start, $end);

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'   => '$paymentMethod',
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['count' => -1]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(fn($r) => [
            'method' => $r['_id'],
            'count'  => (int) $r['count'],
        ], $results);
    }

    // Taux de retour joueurs
    public function getReturningPlayersRate(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = $this->buildDateMatch($start, $end);

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'   => '$userId',
                'count' => ['$sum' => 1],
            ]],
            ['$group' => [
                '_id'      => null,
                'returning' => ['$sum' => ['$cond' => [['$gte' => ['$count', 2]], 1, 0]]],
                'unique'    => ['$sum' => ['$cond' => [['$eq' => ['$count', 1]], 1, 0]]],
            ]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        $r = $results[0] ?? null;

        return [
            'returning' => (int) ($r['returning'] ?? 0),
            'unique'    => (int) ($r['unique'] ?? 0),
        ];
    }

    // Taux de succès par MP (coupable trouvé)
    public function getSuccessRateByMurderParty(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = array_merge(
            !empty($mpIds) ? ['murderPartyId' => ['$in' => $mpIds]] : [],
            $this->buildDateMatch($start, $end, 'playedAt')
        );

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'            => ['murderPartyId' => '$murderPartyId', 'title' => '$murderPartyTitle'],
                'totalSessions'  => ['$sum' => 1],
                'wonSessions'    => ['$sum' => ['$cond' => ['$success', 1, 0]]],
                'totalVotes'     => ['$sum' => '$totalVotes'],
                'correctVotes'   => ['$sum' => '$correctVotes'],
            ]],
            ['$sort' => ['wonSessions' => -1]],
        ];

        $results = $this->dm->getDocumentCollection(GameStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(function ($r) {
            $totalSessions = (int) $r['totalSessions'];
            $wonSessions   = (int) $r['wonSessions'];
            $totalVotes    = (int) $r['totalVotes'];
            $correctVotes  = (int) $r['correctVotes'];

            return [
                'murderPartyId'        => $r['_id']['murderPartyId'] ?? null,
                'title'                => $r['_id']['title'] ?? 'Inconnu',
                'totalSessions'        => $totalSessions,
                'wonSessions'          => $wonSessions,
                'sessionSuccessPercent' => $totalSessions > 0 ? round($wonSessions * 100 / $totalSessions, 1) : 0,
                'totalVotes'           => $totalVotes,
                'correctVotes'         => $correctVotes,
                'playerSuccessPercent' => $totalVotes > 0 ? round($correctVotes * 100 / $totalVotes, 1) : 0,
            ];
        }, $results);
    }

    // CA total (pour le dashboard)
    public function getTotalRevenue(): float
    {
        $pipeline = [
            ['$group' => [
                '_id'          => null,
                'totalRevenue' => ['$sum' => '$amountPaid'],
            ]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        return round((float) ($results[0]['totalRevenue'] ?? 0), 2);
    }

    // Inscriptions par période
    public function getRegistrationsByPeriod(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = $this->buildDateMatch($start, $end, 'registeredAt');

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'   => ['$dateToString' => ['format' => '%Y-%m', 'date' => '$registeredAt']],
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['_id' => 1]],
        ];

        $results = $this->dm->getDocumentCollection(UserRegistrationStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(fn($r) => [
            'month' => $r['_id'],
            'count' => (int) $r['count'],
        ], $results);
    }

    // Web vs App
    public function getSourceDistribution(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $match = $this->buildDateMatch($start, $end);

        $pipeline = [
            ['$match' => $match ?: (object)[]],
            ['$group' => [
                '_id'          => '$source',
                'count'        => ['$sum' => 1],
                'totalRevenue' => ['$sum' => '$amountPaid'],
            ]],
        ];

        $results = $this->dm->getDocumentCollection(PurchaseStat::class)
            ->aggregate($pipeline)->toArray();

        return array_map(fn($r) => [
            'source'       => $r['_id'],
            'count'        => (int) $r['count'],
            'totalRevenue' => round((float) $r['totalRevenue'], 2),
        ], $results);
    }
}