<?php

namespace App\Repository;

use App\Entity\GameResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameResult::class);
    }

    public function getSuccessRateByMurderParty(array $mpIds = [], ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $where = '1=1';
        $params = [];

        if (!empty($mpIds)) {
            $placeholders = implode(',', array_map(fn($i) => ":mp$i", array_keys($mpIds)));
            $where .= " AND mp.id IN ($placeholders)";
            foreach ($mpIds as $i => $id) $params["mp$i"] = $id;
        }
        if ($start) {
            $where .= ' AND gr.completed_at >= :start';
            $params['start'] = $start->format('Y-m-d');
        }
        if ($end) {
            $where .= ' AND gr.completed_at <= :end';
            $params['end'] = $end->format('Y-m-d');
        }

        $sql = "
            SELECT
                mp.id as murder_party_id,
                mp.title as title,
                COUNT(DISTINCT gr.id) as total_sessions,
                SUM(CASE WHEN gr.success = true THEN 1 ELSE 0 END) as won_sessions,
                ROUND(CASE WHEN COUNT(DISTINCT gr.id) > 0
                    THEN SUM(CASE WHEN gr.success = true THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT gr.id)
                    ELSE 0 END, 1) as session_success_percent,
                COUNT(DISTINCT gv.id) as total_votes,
                SUM(CASE WHEN c.is_guilty = true THEN 1 ELSE 0 END) as correct_votes,
                ROUND(CASE WHEN COUNT(DISTINCT gv.id) > 0
                    THEN SUM(CASE WHEN c.is_guilty = true THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT gv.id)
                    ELSE 0 END, 1) as player_success_percent
            FROM murder_parties mp
            LEFT JOIN game_sessions gs ON gs.murder_party_id = mp.id
            LEFT JOIN game_results gr ON gr.game_session_id = gs.id
            LEFT JOIN game_votes gv ON gv.game_session_id = gs.id
            LEFT JOIN characters c ON gv.voted_character_id = c.id
            WHERE {$where}
            GROUP BY mp.id, mp.title
            ORDER BY session_success_percent DESC
        ";

        $results = $conn->executeQuery($sql, $params)->fetchAllAssociative();

        return array_map(fn($r) => [
            'murderPartyId' => $r['murder_party_id'],
            'title' => $r['title'],
            'totalSessions' => (int) $r['total_sessions'],
            'wonSessions' => (int) $r['won_sessions'],
            'sessionSuccessPercent' => (float) $r['session_success_percent'],
            'totalVotes' => (int) $r['total_votes'],
            'correctVotes' => (int) $r['correct_votes'],
            'playerSuccessPercent' => (float) $r['player_success_percent'],
        ], $results);
    }

    public function getRatedVsSold(array $mpIds = []): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $where = '1=1';
        $params = ['status' => 'completed'];

        if (!empty($mpIds)) {
            $placeholders = implode(',', array_map(fn($i) => ":mp$i", array_keys($mpIds)));
            $where .= " AND mp.id IN ($placeholders)";
            foreach ($mpIds as $i => $id) $params["mp$i"] = $id;
        }

        $sql = "
            SELECT
                mp.id as murder_party_id,
                mp.title as title,
                COUNT(DISTINCT p.id) as sold,
                COUNT(DISTINCT gr.id) as rated
            FROM murder_parties mp
            LEFT JOIN purchases p ON p.murder_party_id = mp.id AND p.status = :status
            LEFT JOIN game_sessions gs ON gs.murder_party_id = mp.id
            LEFT JOIN game_ratings gr ON gr.game_session_id = gs.id
            WHERE {$where}
            GROUP BY mp.id, mp.title
            ORDER BY sold DESC
        ";

        $results = $conn->executeQuery($sql, $params)->fetchAllAssociative();

        return array_map(fn($r) => [
            'murderPartyId' => $r['murder_party_id'],
            'title' => $r['title'],
            'sold' => (int) $r['sold'],
            'rated' => (int) $r['rated'],
        ], $results);
    }

}