<?php

namespace App\Controller\Api;

use App\Entity\MurderParty;
use App\Entity\UserMurderParty;
use App\Repository\MurderPartyRepository;
use App\Repository\UserMurderPartyRepository;
use App\Entity\User;
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
        if ($user !== null) {
            $userMurderParties = $userMurderPartyRepository->findBy(['user' => $user]);
            foreach ($userMurderParties as $ump) {
                $purchasedIds[] = $ump->getMurderParty()->getId();
            }
        }

        $data = array_map(function (MurderParty $mp) use ($purchasedIds) {
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
        ]);
    }
}