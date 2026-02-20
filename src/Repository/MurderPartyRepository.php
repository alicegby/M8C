<?php

namespace App\Repository;

use App\Entity\MurderParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MurderPartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MurderParty::class);
    }

    public function getMostPlayed(): array
    {
        return $this->createQueryBuilder('mp')
            ->select('mp.title, COUNT(gs.id) as sessions')
            ->join('App\Entity\GameSession', 'gs', 'WITH', 'gs.murderParty = mp AND gs.status = :status')
            ->setParameter('status', 'finished')
            ->groupBy('mp.id, mp.title')
            ->orderBy('sessions', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}