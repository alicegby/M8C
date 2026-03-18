<?php

namespace App\Controller\Api;

use App\Entity\GamePlayer;
use App\Entity\GameSession;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/game-sessions')]
class GameSessionController extends AbstractController
{
    #[Route('/join', name: 'api_game_session_join', methods: ['POST'])]
    public function join(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $body = json_decode($request->getContent(), true);
        $joinCode = strtoupper(trim($body['joinCode'] ?? ''));

        if (empty($joinCode)) {
            return $this->json(['error' => 'Code manquant'], 400);
        }

        $session = $em->getRepository(GameSession::class)->findOneBy([
            'joinCode' => $joinCode,
            'status'   => 'waiting',
        ]);

        if (!$session) {
            return $this->json(['error' => 'Code invalide ou partie déjà commencée'], 404);
        }

        $mp = $session->getMurderParty();

        // Ajoute le joueur (user optionnel — peut être non connecté)
        $player = new GamePlayer();
        $player->setGameSession($session);
        $player->setPseudoInGame('');
        $player->setIsHost(false);
        $player->setIsReady(false);

        /** @var User|null $user */
        $user = $this->getUser();
        if ($user !== null) {
            $player->setUser($user);
        }

        $em->persist($player);
        $em->flush();

        return $this->json([
            'sessionId' => $session->getId(),
            'joinCode'  => $session->getJoinCode(),
            'scenario'  => [
                'id'         => $mp->getId(),
                'title'      => $mp->getTitle(),
                'nb_players' => $mp->getNbPlayers(),
                'duree'      => $mp->getDuree(),
                'synopsis'   => $mp->getSynopsis(),
                'scenario'   => $mp->getScenario(),
            ],
            'maxPlayers' => $mp->getNbPlayers(),
        ]);
    }
}