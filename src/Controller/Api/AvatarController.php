<?php

namespace App\Controller\Api;

use App\Repository\AvatarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AvatarController extends AbstractController
{
    #[Route('/api/avatars', name: 'api_avatars', methods: ['GET'])]
    public function index(AvatarRepository $avatarRepository): JsonResponse
    {
        $avatars = $avatarRepository->findBy([], ['id' => 'ASC']);

        return $this->json(array_map(fn($a) => [
            'id'        => $a->getId(),
            'image_url' => 'https://meurtrehuisclos.fr' . $a->getImageUrl(),
        ], $avatars));
    }
}