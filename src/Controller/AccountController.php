<?php

namespace App\Controller;

use App\Entity\NewsletterSubscription;
use App\Entity\PromoCode;
use App\Entity\PromoCodeUsage;
use App\Entity\User;
use App\Entity\GamePlayer;
use App\Entity\Purchase;
use App\Form\UserType;
use App\Repository\NewsletterSubscriptionRepository;
use App\Service\BrevoService;
use App\Service\PromoCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(
        NewsletterSubscriptionRepository $newsletterRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verification_pending');
        }

        $isSubscribed = $newsletterRepo->findOneBy(['email' => $user->getEmail()]) !== null;

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

        $purchases = $em->getRepository(Purchase::class)
            ->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.purchasedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('account.html.twig', [
            'user'        => $user,
            'isSubscribed' => $isSubscribed,
            'playedMPs'   => $playedMPs,
            'purchases'   => $purchases,
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

        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $user->setIsVerified(false);
        $em->flush();

        $verificationUrl = $this->generateUrl(
            'app_verify_email',
            ['token' => $token, 'email' => $user->getEmail()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('contact@meurtrehuisclos.fr', 'Meurtre à Huis Clos'))
            ->to($user->getEmail())
            ->subject('Confirmez votre adresse email')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'user'            => $user,
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
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                $user->setAvatarUrl('/uploads/avatars/' . $newFilename);
            }

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
        PromoCodeService $promoCodeService,
        BrevoService $brevoService,
        #[Target('brevo')] MailerInterface $mailer
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $subscription = $newsletterRepo->findOneBy(['email' => $user->getEmail()]);

        if ($subscription) {
            // Désinscription
            $brevoService->unsubscribeContact($user->getEmail());
            $em->remove($subscription);
            $em->flush();
            $this->addFlash('success', 'Vous avez été désinscrit de la newsletter.');
        } else {
            // Inscription
            $subscription = new NewsletterSubscription();
            $subscription->setEmail($user->getEmail());
            $subscription->setUnsubscribeToken(bin2hex(random_bytes(16)));
            $em->persist($subscription);
            $em->flush();

            // Synchronisation avec Brevo
            $brevoService->syncContact($user->getEmail());

            // Envoie le code promo uniquement si jamais reçu
            if (!$this->userAlreadyReceivedNewsletterCode($user, $em)) {
                $promo = $promoCodeService->generateNewsletterCode();

                $email = (new TemplatedEmail())
                    ->from('contact@meurtrehuisclos.fr')
                    ->to($user->getEmail())
                    ->subject('Bienvenue chez Meurtre à Huis Clos | Voici votre code promo')
                    ->htmlTemplate('emails/welcome.html.twig')
                    ->context([
                        'promoCode'        => $promo->getCode(),
                        'app_url'          => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'unsubscribeToken' => $subscription->getUnsubscribeToken(),
                    ]);

                $mailer->send($email);
            }

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

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_account', $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

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
                    ]
                ]);
            } catch (\Exception $e) {
                // Log l'erreur mais continue la suppression
            }
        }

        $em->remove($user);
        $em->flush();

        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        return $this->redirectToRoute('app_home');
    }

    private function userAlreadyReceivedNewsletterCode(User $user, EntityManagerInterface $em): bool
    {
        $usage = $em->getRepository(PromoCodeUsage::class)
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
}