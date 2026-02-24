<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VerifyEmailController extends AbstractController
{
    #[Route('/verification-email', name: 'app_verify_email')]
    public function verifyEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $token = $request->query->get('token');
        $email = $request->query->get('email');

        if (!$token || !$email) {
            $this->addFlash('error', 'Lien de vérification invalide.');
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->findOneBy([
            'email' => $email,
            'emailVerificationToken' => $token,
        ]);

        if (!$user) {
            $this->addFlash('error', 'Lien de vérification invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        $user->setIsVerified(true);
        $user->setEmailVerificationToken(null);
        $em->flush();

        $this->addFlash('success', 'Votre compte est activé ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}