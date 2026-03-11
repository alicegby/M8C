<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\GamePlayer;
use App\Entity\Purchase;
use App\Repository\NewsletterSubscriptionRepository;
use Symfony\Component\DependencyInjection\Attribute\Target;
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
    public function index(
        NewsletterSubscriptionRepository $newsletterRepo,
        EntityManagerInterface $em
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verification_pending');
        }

        $isSubscribed = $newsletterRepo->findOneBy(['email' => $user->getEmail()]) !== null;

        // Récupération des parties jouées par l'utilisateur
        $playedMPs = $em->getRepository(GamePlayer::class)
            ->createQueryBuilder('gp')
            ->join('gp.gameSession', 'gs')
            ->join('gs.murderParty', 'mp')
            ->addSelect('gs', 'mp')
            ->where('gp.user = :user')
            ->setParameter('user', $user)
            ->orderBy('gs.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Récupération des parties achetées par l'utilisateur 
        $purchases = $em->getRepository(Purchase::class)
        ->createQueryBuilder('p')
        ->andWhere('p.user = :user')
        ->setParameter('user', $user)
        ->orderBy('p.purchasedAt', 'DESC')
        ->getQuery()
        ->getResult();

        return $this->render('account.html.twig', [
            'user' => $user,
            'isSubscribed' => $isSubscribed,
            'playedMPs' => $playedMPs, 
            'purchases' => $purchases,
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
    public function toggleNewsletter(
        EntityManagerInterface $em,
        NewsletterSubscriptionRepository $newsletterRepo,
        \App\Service\PromoCodeService $promoCodeService,
        #[Target('brevo')] MailerInterface $mailer
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $subscription = $newsletterRepo->findOneBy(['email' => $user->getEmail()]);

        if ($subscription) {
            // Désinscription
            $em->remove($subscription);
            $em->flush();
            $this->addFlash('success', 'Vous avez été désinscrit de la newsletter.');
        } else {
            // Inscription
            $subscription = new \App\Entity\NewsletterSubscription();
            $subscription->setEmail($user->getEmail());
            $subscription->setUnsubscribeToken(bin2hex(random_bytes(16)));
            $em->persist($subscription);
            $em->flush();

            // Vérifie si l'utilisateur a déjà reçu un code newsletter dans le passé
            $alreadyHadPromo = $em->getRepository(\App\Entity\PromoCode::class)
                ->createQueryBuilder('p')
                ->where('p.code LIKE :prefix')
                ->setParameter('prefix', 'HELLO-%')
                ->getQuery()
                ->getResult();

            // On envoie le code seulement si c'est la première inscription
            if (empty($alreadyHadPromo) || !$this->userAlreadyReceivedNewsletterCode($user, $em)) {
                $promo = $promoCodeService->generateNewsletterCode();

                $email = (new TemplatedEmail())
                    ->from('meurtrehuisclos@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Bienvenue chez Meurtre à Huis Clos | Voici votre code promo')
                    ->htmlTemplate('emails/welcome.html.twig')
                    ->context([
                        'promoCode' => $promo->getCode(),
                        'app_url' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'unsubscribeToken' => $subscription->getUnsubscribeToken(),
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Vous êtes maintenant inscrit à la newsletter.');
        }

        return $this->redirectToRoute('app_account');
    }

    private function userAlreadyReceivedNewsletterCode(User $user, EntityManagerInterface $em): bool
    {
        // Vérifie dans promo_code_usages si l'user a déjà utilisé un code HELLO-
        $usage = $em->getRepository(\App\Entity\PromoCodeUsage::class)
            ->createQueryBuilder('u')
            ->join('u.promoCode', 'p')
            ->where('u.user = :user')
            ->andWhere('p.code LIKE :prefix')
            ->setParameter('user', $user)
            ->setParameter('prefix', 'HELLO-%')
            ->getQuery()
            ->getOneOrNullResult();

        return $usage !== null;
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