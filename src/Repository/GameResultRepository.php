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

    public function getSuccessRateByMurderParty(): array
    {
        return $this->createQueryBuilder('gr')
            ->select('mp.title, COUNT(gr.id) as total, SUM(CASE WHEN gr.success = true THEN 1 ELSE 0 END) as successes')
            ->join('App\Entity\GameSession', 'gs', 'WITH', 'gr.gameSession = gs')
            ->join('gs.murderParty', 'mp')
            ->groupBy('mp.id, mp.title')
            ->getQuery()
            ->getResult();
    }
}