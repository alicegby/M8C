<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserAuthenticatorInterface $userAuthenticator,
        AuthenticationUtils $authenticationUtils,
        #[Autowire(service: 'security.authenticator.form_login.main')] FormLoginAuthenticator $authenticator
    ): Response {

        // Si déjà connecté, redirection
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            // Redirige vers _target_path si présent
            $targetPath = $request->query->get('_target_path');
            if ($targetPath) {
                return $this->redirect($targetPath);
            }
            return $this->redirectToRoute('app_account');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Synchronisation Supabase : récupère l'utilisateur et met à jour supabaseId si vide
        if ($lastUsername) {
            $user = $userRepository->findOneBy(['email' => $lastUsername]);

            if ($user && !$user->getSupabaseId()) {
                $user->setSupabaseId($user->getId());
                $em->persist($user);
                $em->flush();
            }
        }

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'target_path' => $request->query->get('_target_path'),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony gère automatiquement la déconnexion via le firewall
        throw new \LogicException('Cette méthode ne doit jamais être appelée directement.');
    }
}