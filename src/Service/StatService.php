<?php

namespace App\Service;

use App\Document\GameStat;
use App\Document\PurchaseStat;
use App\Document\UserRegistrationStat;
use App\Entity\GameSession;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatService
{
    public function __construct(private DocumentManager $dm) {}

    /**
     * Appelé après chaque achat complété (WebhookController)
     */
    public function recordPurchase(Purchase $purchase, string $source = 'web'): void
    {
        $stat = new PurchaseStat();
        $stat->setUserId($purchase->getUser()->getId());
        $stat->setPurchaseType($purchase->getPurchaseType());
        $stat->setAmountPaid((float) $purchase->getAmountPaid());
        $stat->setDiscountApplied((float) $purchase->getDiscountApplied());
        $stat->setPaymentMethod($purchase->getPaymentMethod());
        $stat->setPurchasedAt($purchase->getPurchasedAt());
        $stat->setSource($source);

        if ($purchase->getMurderParty()) {
            $stat->setMurderPartyId($purchase->getMurderParty()->getId());
            $stat->setMurderPartyTitle($purchase->getMurderParty()->getTitle());
        }

        if ($purchase->getPack()) {
            $stat->setPackId($purchase->getPack()->getId());
            $stat->setPackName($purchase->getPack()->getName());
        }

        if ($purchase->getPromoCode()) {
            $stat->setPromoCodeId($purchase->getPromoCode()->getId());
            $stat->setPromoCode($purchase->getPromoCode()->getCode());
        }

        $this->dm->persist($stat);
        $this->dm->flush();
    }

    /**
     * Appelé à la fin d'une partie (GameSessionController::finish)
     */
    public function recordGame(GameSession $session): void
    {
        $result = $session->getGameResult();
        if (!$result) {
            return;
        }

        $stat = new GameStat();
        $stat->setMurderPartyId($session->getMurderParty()->getId());
        $stat->setMurderPartyTitle($session->getMurderParty()->getTitle());
        $stat->setGameSessionId($session->getId());
        $stat->setPlayerCount($session->getGamePlayers()->count());
        $stat->setSuccess($result->isSuccess());
        $stat->setCorrectVotes($result->getCorrectVotesCount());
        $stat->setTotalVotes($result->getTotalVotesCount());
        $stat->setPlayedAt($result->getCompletedAt());

        // Durée si la partie a été démarrée
        if ($session->getStartedAt()) {
            $duration = $result->getCompletedAt()->getTimestamp() - $session->getStartedAt()->getTimestamp();
            $stat->setDurationSeconds($duration);
        }

        $this->dm->persist($stat);
        $this->dm->flush();
    }

    /**
     * Appelé à l'inscription d'un utilisateur (RegistrationController)
     */
    public function recordRegistration(User $user): void
    {
        $stat = new UserRegistrationStat();
        $stat->setUserId($user->getId());
        $stat->setAuthProvider($user->getAuthProvider());
        $stat->setRegisteredAt($user->getCreatedAt());

        $this->dm->persist($stat);
        $this->dm->flush();
    }
}