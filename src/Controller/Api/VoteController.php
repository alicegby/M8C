<?php

namespace App\Controller\Api;

use App\Entity\GamePlayer;
use App\Entity\GameSession;
use App\Entity\GameVote;
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

            $votes = $em->getRepository(GameVote::class)->findBy(['gameSession' => $session]);
            $players = $em->getRepository(GamePlayer::class)->findBy(['gameSession' => $session]);

            $voteCounts = [];
            $voterIds = [];
            foreach ($votes as $vote) {
                $cid = $vote->getVotedCharacter()->getId();
                $voteCounts[$cid] = ($voteCounts[$cid] ?? 0) + 1;
                $voterIds[$vote->getVoter()->getId()] = true;
            }

            return $this->json([
                'total_voters'  => count($voterIds),
                'players_count' => count($players),
                'votes'         => $voteCounts,
            ]);
        }

        #[Route('/vote', name: 'api_vote_submit', methods: ['POST'])]
        public function submit(
            string $sessionId,
            Request $request,
            EntityManagerInterface $em,
        ): JsonResponse {
            $session = $em->getRepository(GameSession::class)->find($sessionId);
            if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

            $body = json_decode($request->getContent(), true);
            $characterIds = $body['character_ids'] ?? [];
            $playerId = $body['player_id'] ?? null;

            if (empty($characterIds) || !$playerId) {
                return $this->json(['error' => 'Données manquantes'], 400);
            }

            $player = $em->getRepository(GamePlayer::class)->find($playerId);
            if (!$player || $player->getGameSession()->getId() !== $sessionId) {
                return $this->json(['error' => 'Joueur introuvable'], 404);
            }

            // Évite les doublons
            $existing = $em->getRepository(GameVote::class)->findOneBy([
                'gameSession' => $session,
                'voter' => $player,
            ]);
            if ($existing) return $this->json(['error' => 'Déjà voté'], 400);

            foreach ($characterIds as $characterId) {
                $character = $em->getRepository(\App\Entity\Character::class)->find($characterId);
                if (!$character) continue;

                $vote = new GameVote();
                $vote->setGameSession($session);
                $vote->setVoter($player);
                $vote->setVotedCharacter($character);
                $em->persist($vote);
            }

            $em->flush();

            return $this->json(['success' => true]);
        }
    
}