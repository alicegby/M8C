<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        if (!$user->isVerified()) {
            return $this->json(['error' => 'Compte non vérifié. Vérifiez votre email.'], 403);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id'        => $user->getId(),
                'email'     => $user->getEmail(),
                'prenom'    => $user->getPrenom(),
                'nom'       => $user->getNom(),
                'avatarUrl' => $user->getAvatarUrl(),
                'roles'     => $user->getRoles(),
            ],
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'prenom'    => $user->getPrenom(),
            'nom'       => $user->getNom(),
            'avatarUrl' => $user->getAvatarUrl(),
            'roles'     => $user->getRoles(),
        ]);
    }

    #[Route('/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(PurchaseRepository $purchaseRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $purchases = $purchaseRepository->findBy(['user' => $user]);

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'prenom'    => $user->getPrenom(),
            'nom'       => $user->getNom(),
            'pseudo'    => $user->getPseudo(),
            'dob'       => $user->getDob()?->format('Y-m-d'),
            'avatarUrl' => $user->getAvatarUrl(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
            'purchases' => array_map(fn($p) => [
                'id'          => $p->getId(),
                'createdAt'   => $p->getPurchasedAt()->format('Y-m-d'),
                'murderParty' => $p->getMurderParty() ? [
                    'title' => $p->getMurderParty()->getTitle(),
                ] : null,
            ], $purchases),
        ]);
    }
}