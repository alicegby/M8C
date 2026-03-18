<?php

namespace App\Controller\Api;

use App\Entity\GamePlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GamePlayerController extends AbstractController
{
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