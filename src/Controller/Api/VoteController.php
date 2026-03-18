<?php

namespace App\Controller\Api;

use App\Entity\GamePlayer;
use App\Entity\GameSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/game-sessions/{sessionId}')]
class VoteController extends AbstractController
{
    #[Route('/votes', name: 'api_votes_list', methods: ['GET'])]
    public function list(
        string $sessionId,
        EntityManagerInterface $em,
    ): JsonResponse {
        $session = $em->getRepository(GameSession::class)->find($sessionId);
        if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

        // Compte les votes par character_id
        $players = $em->getRepository(GamePlayer::class)->findBy(['gameSession' => $session]);
        $voteCounts = [];
        $totalVoters = 0;

        foreach ($players as $p) {
            if ($p->getVotedCharacterId()) {
                $totalVoters++;
                $cid = $p->getVotedCharacterId();
                $voteCounts[$cid] = ($voteCounts[$cid] ?? 0) + 1;
            }
        }

        return $this->json([
            'total_voters'  => $totalVoters,
            'players_count' => count($players),
            'votes'         => $voteCounts,
        ]);
    }

    #[Route('/vote', name: 'api_vote_submit', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function submit(
        string $sessionId,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $session = $em->getRepository(GameSession::class)->find($sessionId);
        if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

        $user = $this->getUser();
        $player = null;
        foreach ($session->getGamePlayers() as $p) {
            if ($p->getUser() === $user) { $player = $p; break; }
        }
        if (!$player) return $this->json(['error' => 'Joueur introuvable'], 404);

        $body = json_decode($request->getContent(), true);
        $characterIds = $body['character_ids'] ?? [];

        if (empty($characterIds)) return $this->json(['error' => 'Aucun vote'], 400);

        // On stocke le premier vote (ou les deux) dans le player
        $player->setVotedCharacterId(implode(',', $characterIds));
        $em->flush();

        return $this->json(['success' => true]);
    }
}