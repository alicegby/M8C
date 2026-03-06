<?php

namespace App\Service;

use App\Entity\GameSession;
use App\Entity\Purchase;
use App\Entity\User;

class StatService
{
    public function __construct(private MongoService $mongo) {}

    public function recordPurchase(Purchase $purchase, string $source = 'web'): void
    {
        $data = [
            'userId'        => $purchase->getUser()->getId(),
            'purchaseType'  => $purchase->getPurchaseType(),
            'amountPaid'    => (float) $purchase->getAmountPaid(),
            'discountApplied' => (float) $purchase->getDiscountApplied(),
            'paymentMethod' => $purchase->getPaymentMethod(),
            'purchasedAt'   => new \MongoDB\BSON\UTCDateTime($purchase->getPurchasedAt()->getTimestamp() * 1000),
            'source'        => $source,
        ];

        if ($purchase->getMurderParty()) {
            $data['murderPartyId']    = $purchase->getMurderParty()->getId();
            $data['murderPartyTitle'] = $purchase->getMurderParty()->getTitle();
        }

        if ($purchase->getPack()) {
            $data['packId']   = $purchase->getPack()->getId();
            $data['packName'] = $purchase->getPack()->getName();
        }

        if ($purchase->getPromoCode()) {
            $data['promoCodeId'] = $purchase->getPromoCode()->getId();
            $data['promoCode']   = $purchase->getPromoCode()->getCode();
        }

        $this->mongo->savePurchase($data);
    }

    public function recordGame(GameSession $session): void
    {
        $result = $session->getGameResult();
        if (!$result) return;

        $data = [
            'murderPartyId'    => $session->getMurderParty()->getId(),
            'murderPartyTitle' => $session->getMurderParty()->getTitle(),
            'gameSessionId'    => $session->getId(),
            'playerCount'      => $session->getGamePlayers()->count(),
            'success'          => $result->isSuccess(),
            'correctVotes'     => $result->getCorrectVotesCount(),
            'totalVotes'       => $result->getTotalVotesCount(),
            'playedAt'         => new \MongoDB\BSON\UTCDateTime($result->getCompletedAt()->getTimestamp() * 1000),
        ];

        if ($session->getStartedAt()) {
            $data['durationSeconds'] = $result->getCompletedAt()->getTimestamp() - $session->getStartedAt()->getTimestamp();
        }

        $this->mongo->saveGame($data);
    }

    public function recordRegistration(User $user): void
    {
        $this->mongo->saveRegistration(
            $user->getId(),
            $user->getAuthProvider(),
            $user->getCreatedAt()
        );
    }
}