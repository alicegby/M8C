<?php

namespace App\Controller\Api;

use App\Entity\GameSession;
use App\Repository\GameSessionRepository;
use App\Entity\GameVote;
use App\Repository\MurderPartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EpilogueController extends AbstractController
{
    #[Route('/api/game-sessions/{sessionId}/epilogue', name: 'api_epilogue', methods: ['GET'])]
    public function index(string $sessionId, EntityManagerInterface $em): JsonResponse
    {
        $session = $em->getRepository(GameSession::class)->find($sessionId);
        if (!$session) return $this->json(['error' => 'Session introuvable'], 404);

        $players = $session->getGamePlayers()->toArray();
        if (empty($players)) return $this->json(['error' => 'Aucun joueur'], 404);

        $killerPlayer = null;
        $killerChar = null;
        foreach ($players as $p) {
            $char = $p->getCharacter();
            if ($char && $char->isGuilty()) {
                $killerPlayer = $p;
                $killerChar = $char;
                break;
            }
        }
        if (!$killerPlayer) return $this->json(['error' => 'Coupable introuvable'], 404);

        $votes = $em->getRepository(GameVote::class)->findBy(['gameSession' => $session]);
        $totalVoters = count(array_unique(array_map(fn($v) => $v->getVoter()->getId(), $votes)));
        $correctVotes = count(array_filter($votes, fn($v) => $v->getVotedCharacter()->getId() === $killerChar->getId()));
        $won = $correctVotes > count($players) / 2;

        return $this->json([
            'won'          => $won,
            'correctVotes' => $correctVotes,
            'totalVoters'  => $totalVoters,
            'epilogue'     => $session->getMurderParty()->getEpilogue() ?? '',
            'killer' => [
                'pseudo' => $killerPlayer->getPseudoInGame(),
                'avatar' => $killerPlayer->getAvatarInGame(),
                'prenom' => $killerChar->getPrenom(),
                'nom'    => $killerChar->getNom(),
            ],
        ]);
    }

    #[Route('/api/murder-parties/{id}/rate', name: 'api_rate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function rate(
        string $id,
        Request $request,
        MurderPartyRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $mp = $repo->find($id);
        if (!$mp) return $this->json(['error' => 'Introuvable'], 404);

        $body = json_decode($request->getContent(), true);
        $rating = (int)($body['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) return $this->json(['error' => 'Note invalide'], 400);

        $count = $mp->getRatingsCount() ?? 0;
        $avg   = $mp->getAverageRating() ?? 0.0;
        $mp->setRatingsCount($count + 1);
        $mp->setAverageRating((($avg * $count) + $rating) / ($count + 1));
        $em->flush();

        return $this->json(['success' => true]);
    }
}