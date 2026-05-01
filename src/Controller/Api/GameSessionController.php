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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            'playerId' => $player->getId(),
            'scenario'  => [
                'id'         => $mp->getId(),
                'title'      => $mp->getTitle(),
                'nbPlayers'     => $mp->getNbPlayers(),
                'duree'      => $mp->getDuree(),
                'synopsis'   => $mp->getSynopsis(),
                'scenario'   => $mp->getScenario(),
                'averageRating' => $mp->getAverageRating(),
            ],
            'maxPlayers' => $mp->getNbPlayers(),
        ]);
    }

    #[Route('/{sessionId}/players', name: 'api_game_session_players', methods: ['GET'], requirements: ['sessionId' => '[0-9a-f-]{36}'])]
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

        $data = array_map(function($p) {
            $character = $p->getCharacter(); // ← récupère le personnage ici
            return [
                'id'           => $p->getId(),
                'user_id'      => $p->getUser()?->getId(),
                'pseudo'       => $p->getPseudoInGame() ?? '',
                'avatar'       => $p->getAvatarInGame(),
                'is_ready'     => $p->isReady(),
                'character_id' => $character?->getId(), // maintenant OK
                'character'    => $character ? [
                    'id'       => $character->getId(),
                    'nom'      => $character->getNom(),
                    'prenom'   => $character->getPrenom(),
                    'age'      => $character->getAge(),
                    'job'      => $character->getJob(),
                    'histoire' => $character->getHistoire(),
                    'mobile'   => $character->getMobile(),
                    'alibi'    => $character->getAlibi(),
                    'extra'    => $character->getExtraInfo(),
                    'isGuilty' => $character->isGuilty(),
                ] : null,
            ];
        }, $players);

        return $this->json($data);
    }

    #[Route('/my-character/{joinCode}', name: 'api_game_session_my_character', methods: ['GET'])]
    public function myCharacter(string $joinCode, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $session = $em->getRepository(GameSession::class)
            ->findOneBy(['joinCode' => strtoupper($joinCode)]);
        if (!$session) {
            return $this->json(['error' => 'Partie introuvable'], 404);
        }

        $player = $em->getRepository(GamePlayer::class)
            ->findOneBy(['gameSession' => $session, 'user' => $user]);
        if (!$player) {
            return $this->json(['error' => 'Joueur non trouvé dans la partie'], 404);
        }

        $character = $player->getCharacter();
        if (!$character) {
            return $this->json(['error' => 'Personnage non assigné'], 404);
        }

        return $this->json([
            'character' => [
                'id'        => $character->getId(),
                'prenom'    => $character->getPrenom(),
                'nom'       => $character->getNom(),
                'age'       => $character->getAge(),
                'job'       => $character->getJob(),
                'histoire'  => $character->getHistoire(),
                'mobile'    => $character->getMobile(),
                'alibi'     => $character->getAlibi(),
                'extraInfo' => $character->getExtraInfo(),
                'isGuilty'  => $character->isGuilty(),
            ],
        ]);
    }

    #[Route('/{sessionId}', name: 'api_game_session_get', methods: ['GET'], requirements: ['sessionId' => '[0-9a-f-]{36}'])]
    public function getSession(
        string $sessionId,
        EntityManagerInterface $em,
    ): JsonResponse {
        $session = $em->getRepository(GameSession::class)->find($sessionId);
        if (!$session) {
            return $this->json(['error' => 'Session introuvable'], 404);
        }

        return $this->json([
            'id'         => $session->getId(),
            'joinCode'   => $session->getJoinCode(),
            'status'     => $session->getStatus(),
            'maxPlayers' => $session->getMurderParty()->getNbPlayers(),
        ]);
    }

    #[Route('/{joinCode}/start', name: 'api_game_session_start', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function start(string $joinCode, EntityManagerInterface $em): JsonResponse
    {
        $session = $em->getRepository(GameSession::class)->findOneBy([
            'joinCode' => strtoupper($joinCode),
        ]);
        if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

        // Distribue les personnages
        $players = $em->getRepository(GamePlayer::class)->findBy([
            'gameSession' => $session,
        ]);
        $characters = $session->getMurderParty()->getCharacters()->toArray();

        if (empty($characters)) {
            return $this->json(['error' => 'Aucun personnage trouvé pour ce scénario'], 400);
        }
        if (empty($players)) {
            return $this->json(['error' => 'Aucun joueur pour cette session'], 400);
        }
        if (count($players) > count($characters)) {
            return $this->json(['error' => 'Pas assez de personnages (' . count($characters) . ' pour ' . count($players) . ' joueurs)'], 400);
        }

        shuffle($characters);
        foreach ($players as $index => $player) {
            $player->setCharacter($characters[$index]);
            $player->setIsReady(false);
        }

        $session->setStatus('started');
        $em->persist($session);
        $em->flush();

        return $this->json([
            'success' => true,
            'debug' => [
                'nbPlayers'    => count($players),
                'nbCharacters' => count($characters),
            ],
        ]);
    }

    #[Route('/{joinCode}/next', name: 'api_game_session_next', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function next(string $joinCode, EntityManagerInterface $em): JsonResponse
    {
        $session = $em->getRepository(GameSession::class)->findOneBy([
            'joinCode' => strtoupper($joinCode),
        ]);
        if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

        $session->setStatus('characters');
        $em->flush();

        return $this->json(['success' => true]);
    }
}