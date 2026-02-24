<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(NewsletterSubscriptionRepository $newsletterRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Redirige si l'utilisateur n'a pas confirmé son email
        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verification_pending');
        }

        $isSubscribed = $newsletterRepo->findOneBy(['email' => $user->getEmail()]) !== null;

        return $this->render('account.html.twig', [
            'user' => $user,
            'isSubscribed' => $isSubscribed,
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
    #[Route('/mon-compte/modification', name: 'account_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de l'upload d'avatar
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();
                $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                $user->setAvatarUrl('/uploads/avatars/'.$newFilename);
            }

            // Gestion de la suppression d'avatar
            $removeAvatar = $form->get('removeAvatar')->getData();
            if ($removeAvatar) {
                $user->setAvatarUrl(null);
            }

            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mon-compte/newsletter/toggle', name: 'account_toggle_newsletter', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleNewsletter(EntityManagerInterface $em, \App\Repository\NewsletterSubscriptionRepository $newsletterRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        // Vérifie si l'utilisateur est déjà inscrit
        $subscription = $newsletterRepo->findOneBy(['email' => $user->getEmail()]);

        if ($subscription) {
            // Si oui, on se désinscrit
            $em->remove($subscription);
            $em->flush();
            $this->addFlash('success', 'Vous avez été désinscrit de la newsletter.');
        } else {
            // Sinon, on crée une nouvelle inscription
            $subscription = new \App\Entity\NewsletterSubscription();
            $subscription->setEmail($user->getEmail());
            $em->persist($subscription);
            $em->flush();
            $this->addFlash('success', 'Vous êtes maintenant inscrit à la newsletter.');
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/suppression', name: 'account_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        // Vérifie le token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_account', $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Supprime l'utilisateur
        $em->remove($user);
        $em->flush();

        // Déconnecte l'utilisateur
        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        return $this->redirectToRoute('app_home'); // redirige vers l'accueil ou une page spécifique
    }
}