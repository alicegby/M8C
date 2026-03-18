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

    #[Route('/{sessionId}/players', name: 'api_game_session_players', methods: ['GET'])]
    public function players(
        string $sessionId,
        EntityManagerInterface $em,
    ): JsonResponse {
        $session = $em->getRepository(GameSession::class)->find($sessionId);
        if (!$session) {
            return $this->json(['error' => 'Session introuvable'], 404);
        }

        $players = $em->getRepository(GamePlayer::class)->findBy([
            'gameSession' => $session,
        ]);

        $data = array_map(fn($p) => [
            'id'           => $p->getId(),
            'pseudo'       => $p->getPseudoInGame(),
            'avatar'       => $p->getAvatarInGame(),
        ], $players);

        return $this->json($data);
    }

    #[Route('/api/game-players/{id}', name: 'api_game_player_update', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        string $id,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $player = $em->getRepository(GamePlayer::class)->find($id);
        if (!$player) {
            return $this->json(['error' => 'Joueur introuvable'], 404);
        }

        $body = json_decode($request->getContent(), true);

        if (isset($body['pseudo_in_game'])) {
            $player->setPseudoInGame($body['pseudo_in_game']);
        }
        if (isset($body['avatar_in_game'])) {
            $player->setAvatarInGame($body['avatar_in_game']);
        }
        if (isset($body['is_ready'])) {
            $player->setIsReady($body['is_ready']);
        }

        $em->flush();

        return $this->json(['success' => true]);
    }
}