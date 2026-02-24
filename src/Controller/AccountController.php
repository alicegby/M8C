<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Redirige si l'utilisateur n'a pas confirmé son email
        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verification_pending');
        }

        return $this->render('account.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/verification-en-attente', name: 'app_verification_pending')]
    #[IsGranted('ROLE_USER')]
    public function verificationPending(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('verification_pending.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/renvoyer-email-verification', name: 'app_resend_verification_email')]
    #[IsGranted('ROLE_USER')]
    public function resendVerificationEmail(EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        // Génère un token unique
        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $user->setIsVerified(false);
        $em->flush();

        // Génère le lien complet
        $verificationUrl = $this->generateUrl(
            'app_verify_email',
            ['token' => $token, 'email' => $user->getEmail()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Envoie l'email
        $emailMessage = (new TemplatedEmail())
            ->from(new Address('meurtrehuisclos@gmail.com', 'Meurtre à Huis Clos'))
            ->to($user->getEmail())
            ->subject('Confirmez votre adresse email')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ]);

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Un nouvel email de confirmation a été envoyé.');
        return $this->redirectToRoute('app_verification_pending');
    }
}