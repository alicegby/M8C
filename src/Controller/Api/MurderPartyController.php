<?php

namespace App\Controller\Api;

use App\Entity\MurderParty;
use App\Entity\UserMurderParty;
use App\Repository\MurderPartyRepository;
use App\Repository\UserMurderPartyRepository;
use App\Entity\User;
use App\Entity\GameSession;
use App\Entity\GamePlayer;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/murder-parties')]
class MurderPartyController extends AbstractController
{
    #[Route('', name: 'api_murder_parties', methods: ['GET'])]
    public function index(
        MurderPartyRepository $murderPartyRepository,
        UserMurderPartyRepository $userMurderPartyRepository,
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();

        $murderParties = $murderPartyRepository->findBy(
            ['isPublished' => true],
            ['averageRating' => 'DESC']
        );

        // Récupère les IDs des scénarios achetés par l'user
        $purchasedIds = [];
        $playedIds = [];
        if ($user !== null) {
            $userMurderParties = $userMurderPartyRepository->findBy(['user' => $user]);
            foreach ($userMurderParties as $ump) {
                $purchasedIds[] = $ump->getMurderParty()->getId();
                if ($ump->isPlayed()) {
                    $playedIds[] = $ump->getMurderParty()->getId();
                }
            }
        }

        $data = array_map(function (MurderParty $mp) use ($purchasedIds, $playedIds) {
            return [
                'id'            => $mp->getId(),
                'title'         => $mp->getTitle(),
                'synopsis'      => $mp->getSynopsis(),
                'scenario'      => $mp->getScenario(),
                'duree'         => $mp->getDuree(),
                'nbPlayers'     => $mp->getNbPlayers(),
                'price'         => $mp->getPrice(),
                'isFree'        => $mp->isFree(),
                'averageRating' => $mp->getAverageRating(),
                'userPurchased' => in_array($mp->getId(), $purchasedIds),
                'userPlayed'    => in_array($mp->getId(), $playedIds),
            ];
        }, $murderParties);

        return $this->json($data);
    }

    #[Route('/{id}/ownership', name: 'api_murder_party_ownership', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function ownership(
        string $id,
        MurderPartyRepository $murderPartyRepository,
        UserMurderPartyRepository $userMurderPartyRepository,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $murderParty = $murderPartyRepository->find($id);
        if (!$murderParty) {
            return $this->json(['error' => 'Scénario introuvable'], 404);
        }

        $ump = $userMurderPartyRepository->findOneBy([
            'user'        => $user,
            'murderParty' => $murderParty,
        ]);

        if (!$ump) {
            return $this->json(['owned' => false, 'withinRetractionWindow' => false]);
        }

        // Vérifie la fenêtre de rétractation (14 jours)
        $withinRetractionWindow = false;
        if (!$ump->isPlayed() && $ump->getPurchase() !== null) {
            $purchase = $ump->getPurchase();
            if ($purchase->getPlayedAt() === null) {
                $limit = \DateTime::createFromInterface($purchase->getPurchasedAt())
                    ->modify('+14 days');
                $withinRetractionWindow = new \DateTime() <= $limit;
            }
        }

        return $this->json([
            'owned'                  => true,
            'withinRetractionWindow' => $withinRetractionWindow,
            'isPlayed'               => $ump->isPlayed(),
        ]);
    }

    #[Route('/{id}/session', name: 'api_murder_party_create_session', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createSession(
        string $id,
        MurderPartyRepository $murderPartyRepository,
        UserMurderPartyRepository $userMurderPartyRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $murderParty = $murderPartyRepository->find($id);
        if (!$murderParty) {
            return $this->json(['error' => 'Scénario introuvable'], 404);
        }

        // Vérif ownership si payant
        if (!$murderParty->isFree()) {
            $ump = $userMurderPartyRepository->findOneBy([
                'user' => $user,
                'murderParty' => $murderParty,
            ]);
            if (!$ump) {
                return $this->json(['error' => 'Scénario non acheté'], 403);
            }
        }

        // Génère un join code unique
        $joinCode = null;
        $attempts = 0;
        do {
            $candidate = $this->generateJoinCode();
            $existing = $em->getRepository(GameSession::class)->findOneBy(['joinCode' => $candidate]);
            if (!$existing) {
                $joinCode = $candidate;
            }
            if (++$attempts > 5) {
                return $this->json(['error' => 'Impossible de générer un code'], 500);
            }
        } while ($joinCode === null);

        // Crée la session
        $session = new GameSession();
        $session->setMurderParty($murderParty);
        $session->setHostUser($user);
        $session->setJoinCode($joinCode);
        $session->setStatus('waiting');
        $em->persist($session);

        // Ajoute le host comme premier joueur
        $player = new GamePlayer();
        $player->setGameSession($session);
        $player->setUser($user);
        $em->persist($player);

        $em->flush();

        return $this->json([
            'joinCode'  => $joinCode,
            'sessionId' => $session->getId(),
            'playerId'  => $player->getId(),
        ]);
    }

    #[Route('/purchases/confirm', name: 'api_purchase_confirm', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function confirmPurchase(
        Request $request,
        MurderPartyRepository $murderPartyRepository,
        UserMurderPartyRepository $userMurderPartyRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $body = json_decode($request->getContent(), true);
        $murderPartyId = $body['murderPartyId'] ?? null;
        $appleReceiptId = $body['appleReceiptId'] ?? '';
        $amount = $body['amount'] ?? '0.00';

        $murderParty = $murderPartyRepository->find($murderPartyId);
        if (!$murderParty) {
            return $this->json(['error' => 'Scénario introuvable'], 404);
        }

        // Évite les doublons
        $existing = $userMurderPartyRepository->findOneBy([
            'user' => $user,
            'murderParty' => $murderParty,
        ]);
        if ($existing) {
            return $this->json(['success' => true, 'message' => 'Déjà acheté']);
        }

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setMurderParty($murderParty);
        $purchase->setPurchaseType('single');
        $purchase->setAmountPaid($amount);
        $purchase->setDiscountApplied('0.00');
        $purchase->setPaymentMethod('apple_pay');
        $purchase->setStripePaymentId($appleReceiptId);
        $purchase->setSource('app');
        $purchase->setStatus('completed');
        $em->persist($purchase);

        $ump = new UserMurderParty();
        $ump->setUser($user);
        $ump->setMurderParty($murderParty);
        $ump->setPurchase($purchase);
        $ump->setIsPlayed(false);
        $em->persist($ump);

        $em->flush();

        return $this->json(['success' => true]);
    }

    private function generateJoinCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

}