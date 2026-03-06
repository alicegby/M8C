<?php

namespace App\Controller;

use App\Entity\GameSession;
use App\Entity\GamePlayer;
use App\Entity\MurderParty;
use App\Repository\GameSessionRepository;
use App\Service\JoinCodeGenerator;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/game-session')]
class GameSessionController extends AbstractController
{
    /* CREATE GAME SESSION */
    #[Route('/create/{id}', name: 'game_session_create', methods: ['POST'])]
    public function create(
        MurderParty $murderParty,
        JoinCodeGenerator $codeGenerator,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $session = new GameSession();
        $session->setMurderParty($murderParty);
        $session->setHostUser($user);
        $session->setJoinCode($codeGenerator->generate());
        $session->setStatus('waiting');

        $em->persist($session);
        $em->flush();

        return new JsonResponse([
            'id' => $session->getId(),
            'joinCode' => $session->getJoinCode(),
            'status' => $session->getStatus(),
        ]);
    }

    /* JOIN GAME SESSION */
    #[Route('/join', name: 'game_session_join', methods: ['POST'])]
    public function join(
        Request $request,
        GameSessionRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $code = strtoupper($request->request->get('code'));
        $session = $repository->findOneBy(['joinCode' => $code]);

        if (!$session || $session->getStatus() === 'finished') {
            return new JsonResponse(['error' => 'Code invalide ou partie terminée'], 400);
        }

        foreach ($session->getGamePlayers() as $player) {
            if ($player->getUser() === $user) {
                return new JsonResponse(['message' => 'Déjà dans la partie']);
            }
        }

        $gamePlayer = new GamePlayer();
        $gamePlayer->setGameSession($session);
        $gamePlayer->setUser($user);

        $em->persist($gamePlayer);
        $em->flush();

        return new JsonResponse(['message' => 'Partie rejointe']);
    }

    /* START GAME (HOST ONLY) */
    #[Route('/start/{code}', name: 'game_session_start', methods: ['POST'])]
    public function start(
        string $code,
        GameSessionRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $session = $repository->findOneBy(['joinCode' => strtoupper($code)]);

        if (!$session) {
            return new JsonResponse(['error' => 'Session introuvable'], 404);
        }

        if ($session->getHostUser() !== $user) {
            return new JsonResponse(['error' => 'Only host can start'], 403);
        }

        $characters = $session->getMurderParty()->getCharacters()->toArray();
        $players = $session->getGamePlayers()->toArray();

        if (count($players) > count($characters)) {
            return new JsonResponse(['error' => 'Pas assez de personnages pour tous les joueurs'], 400);
        }

        shuffle($characters);

        foreach ($players as $index => $player) {
            $player->setCharacter($characters[$index]);
        }

        $session->setStatus('playing');
        $session->setStartedAt(new \DateTime());

        $em->flush();

        return new JsonResponse([
            'message' => 'Partie démarrée et personnages distribués',
        ]);
    }

    /* FINISH GAME */
    #[Route('/finish/{code}', name: 'game_session_finish', methods: ['POST'])]
    public function finish(
        string $code,
        GameSessionRepository $repository,
        EntityManagerInterface $em,
        StatService $statService,
    ): JsonResponse {
        $user = $this->getUser();
        $session = $repository->findOneBy(['joinCode' => strtoupper($code)]);

        if (!$session) {
            return new JsonResponse(['error' => 'Session introuvable'], 404);
        }

        if ($session->getHostUser() !== $user) {
            return new JsonResponse(['error' => 'Only host can finish'], 403);
        }

        $session->setStatus('finished');
        $em->flush();

        // Enregistrement stat avant suppression
        $statService->recordGame($session);

        $em->remove($session);
        $em->flush();

        return new JsonResponse(['message' => 'Partie terminée et supprimée']);
    }

    /* GET MY CHARACTER */
    #[Route('/my-character/{code}', name: 'game_session_my_character', methods: ['GET'])]
    public function myCharacter(
        string $code,
        GameSessionRepository $repository
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $session = $repository->findOneBy(['joinCode' => strtoupper($code)]);
        if (!$session) {
            return new JsonResponse(['error' => 'Session introuvable'], 404);
        }

        $player = null;
        foreach ($session->getGamePlayers() as $p) {
            if ($p->getUser() === $user) {
                $player = $p;
                break;
            }
        }

        if (!$player || !$player->getCharacter()) {
            return new JsonResponse(['error' => 'Personnage non assigné'], 400);
        }

        $character = $player->getCharacter();

        return new JsonResponse([
            'pseudo' => $player->getPseudoInGame(),
            'avatar' => $player->getAvatarInGame(),
            'character' => [
                'prenom' => $character->getPrenom(),
                'nom' => $character->getNom(),
                'age' => $character->getAge(),
                'job' => $character->getJob(),
                'histoire' => $character->getHistoire(),
                'alibi' => $character->getAlibi(),
                'extraInfo' => $character->getExtraInfo(),
                'isGuilty' => $character->isGuilty(),
            ],
        ]);
    }
}