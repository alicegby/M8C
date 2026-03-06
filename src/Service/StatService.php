<?php

namespace App\Service;

use App\Entity\GameSession;
use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\StatRepository;

class StatService
{
    public function __construct(private StatRepository $statRepo) {}

    public function recordPurchase(Purchase $purchase, string $source = 'web'): void
    {
        $this->statRepo->savePurchaseStat($purchase, $source);
    }

    public function recordGame(GameSession $session): void
    {
        $this->statRepo->saveGameStat($session);
    }

    public function recordRegistration(User $user): void
    {
        $this->statRepo->saveRegistrationStat($user);
    }
}