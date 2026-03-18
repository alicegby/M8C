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
}