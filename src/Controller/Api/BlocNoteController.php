<?php

namespace App\Controller\Api;

use App\Entity\BlocNote;
use App\Entity\GamePlayer;
use App\Entity\GameSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/game-players/{playerId}/notes')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BlocNoteController extends AbstractController
{
    #[Route('', name: 'api_notes_list', methods: ['GET'])]
    public function list(
        string $playerId,
        EntityManagerInterface $em,
    ): JsonResponse {
        $player = $em->getRepository(GamePlayer::class)->find($playerId);
        if (!$player) return $this->json(['error' => 'Joueur introuvable'], 404);

        $notes = $em->getRepository(BlocNote::class)->findBy(
            ['gamePlayer' => $player],
            ['createdAt' => 'DESC']
        );

        return $this->json(array_map(fn($n) => [
            'id'         => $n->getId(),
            'content'    => $n->getContent(),
            'created_at' => $n->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $notes));
    }

    #[Route('', name: 'api_notes_create', methods: ['POST'])]
    public function create(
        string $playerId,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $player = $em->getRepository(GamePlayer::class)->find($playerId);
        if (!$player) return $this->json(['error' => 'Joueur introuvable'], 404);

        $body = json_decode($request->getContent(), true);
        $content = trim($body['content'] ?? '');
        if (empty($content)) return $this->json(['error' => 'Contenu vide'], 400);

        $note = new BlocNote();
        $note->setGamePlayer($player);
        $note->setGameSession($player->getGameSession());
        $note->setContent($content);

        $em->persist($note);
        $em->flush();

        return $this->json([
            'id'         => $note->getId(),
            'content'    => $note->getContent(),
            'created_at' => $note->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }
}