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
    public function profile(
        PurchaseRepository $purchaseRepository,
        EntityManagerInterface $em,
        ): JsonResponse{
        /** @var User $user */
        $user = $this->getUser();

        $purchases = $purchaseRepository->findBy(['user' => $user]);

        $playedGames = $em->getRepository(\App\Entity\GamePlayer::class)->findBy([
            'user' => $user,
        ]);

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
                'status'      => $p->getStatus(),
                'createdAt'   => $p->getPurchasedAt()->format('Y-m-d'),
                'murderParty' => $p->getMurderParty() ? [
                    'title' => $p->getMurderParty()->getTitle(),
                ] : null,
                ], $purchases),
                'playedGames' => array_map(fn($gp) => [
                'gameSession' => [
                    'createdAt'   => $gp->getGameSession()->getCreatedAt()->format('Y-m-d'),
                    'murderParty' => [
                        'title' => $gp->getGameSession()->getMurderParty()->getTitle(),
                    ],
                ],
            ], array_filter($playedGames, fn($gp) => $gp->getGameSession()->getStatus() === 'finished')),
        ]);
    }

    #[Route('/account', name: 'api_delete_account', methods: ['DELETE'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // Même logique que AccountController::deleteAccount
        foreach ($em->getRepository(\App\Entity\Purchase::class)->findBy(['user' => $user]) as $purchase) {
            $em->remove($purchase);
        }
        foreach ($em->getRepository(\App\Entity\PromoCodeUsage::class)->findBy(['user' => $user]) as $usage) {
            $em->remove($usage);
        }
        foreach ($em->getRepository(\App\Entity\GameSession::class)->findBy(['hostUser' => $user]) as $gs) {
            foreach ($em->getRepository(\App\Entity\GamePlayer::class)->findBy(['gameSession' => $gs]) as $gp) {
                $em->remove($gp);
            }
            $em->remove($gs);
        }
        foreach ($em->getRepository(\App\Entity\GamePlayer::class)->findBy(['user' => $user]) as $gp) {
            $em->remove($gp);
        }
        foreach ($em->getRepository(\App\Entity\UserMurderParty::class)->findBy(['user' => $user]) as $ump) {
            $em->remove($ump);
        }
        foreach ($em->getRepository(\App\Entity\PushToken::class)->findBy(['user' => $user]) as $pt) {
            $em->remove($pt);
        }

        $newsletter = $em->getRepository(\App\Entity\NewsletterSubscription::class)
            ->findOneBy(['email' => $user->getEmail()]);
        if ($newsletter) {
            $em->remove($newsletter);
        }

        $em->flush();

        // Suppression dans Supabase auth.users
        if ($user->getSupabaseId()) {
            $supabaseUrl = $_ENV['SUPABASE_URL'];
            $supabaseServiceKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY'];
            $client = new \GuzzleHttp\Client();
            try {
                $client->delete("{$supabaseUrl}/auth/v1/admin/users/{$user->getSupabaseId()}", [
                    'headers' => [
                        'Authorization' => "Bearer {$supabaseServiceKey}",
                        'apikey' => $supabaseServiceKey,
                    ],
                ]);
            } catch (\Exception $e) {
                // Log mais on continue
            }
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Compte supprimé avec succès'], 200);
    }
}