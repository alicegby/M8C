<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Client;

class RegistrationController extends AbstractController
{
    private string $supabaseUrl;
    private string $supabaseKey;
    private string $recaptchaSecretKey;
    private string $recaptchaSiteKey;

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'];
        $this->supabaseKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY'];
        $this->recaptchaSecretKey = $_ENV['RECAPTCHA_SECRET_KEY'];
        $this->recaptchaSiteKey = $_ENV['RECAPTCHA_SITE_KEY'];
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        StatService $statService
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ── 0 Vérification reCAPTCHA ──────────────────────────
            if (!$this->verifyCaptcha($request)) {
                $this->addFlash('error', 'Échec de la vérification anti-robot. Réessayez.');
                return $this->redirectToRoute('app_register');
            }

            // Vérifie si email déjà utilisé
            $existing = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existing) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
                return $this->redirectToRoute('app_register');
            }

            $plainPassword = $form->get('plainPassword')->getData();

            // ── 1 Création utilisateur dans Supabase Auth ─────────
            try {
                $client = new Client(); // GuzzleHttp\Client
                $response = $client->request('POST', "{$this->supabaseUrl}/auth/v1/admin/users", [
                    'headers' => [
                        'apikey' => $this->supabaseKey,
                        'Authorization' => "Bearer {$this->supabaseKey}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'email' => $user->getEmail(),
                        'password' => $plainPassword,
                        'email_confirm' => false,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $supabaseUserId = $data['id'] ?? null;

                if (!$supabaseUserId) {
                    throw new \Exception('Impossible de créer l’utilisateur Supabase');
                }

                $user->setSupabaseId($supabaseUserId);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du compte Supabase : ' . $e->getMessage());
                return $this->redirectToRoute('app_register');
            }

            // ── 2 Hash mot de passe et setup Symfony ─────────────
            $user->setPasswordHash($passwordHasher->hashPassword($user, $plainPassword));

            // Génère token de vérification email
            $token = bin2hex(random_bytes(32));
            $user->setEmailVerificationToken($token);
            $user->setIsVerified(false);

            $em->persist($user);
            $em->flush();

            // ── Inscription newsletter si cochée ────────────────────────
            if ($user->isNewsletter()) {
                $existing = $em->getRepository(\App\Entity\NewsletterSubscription::class)
                    ->findOneBy(['email' => $user->getEmail()]);

                if (!$existing) {
                    $subscription = new \App\Entity\NewsletterSubscription();
                    $subscription->setEmail($user->getEmail());
                    $subscription->setUnsubscribeToken(bin2hex(random_bytes(32)));
                    $em->persist($subscription);
                    $em->flush();
                }
            }

             // ── 3 Enregistrement stat ────────────────────
            $statService->recordRegistration($user);

            // ── 4 Envoi email de vérification ────────────────────
            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $token, 'email' => $user->getEmail()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('meurtrehuisclos@gmail.com')
                ->to($user->getEmail())
                ->subject('Confirmez votre inscription — Meurtre à Huis Clos')
                ->html($this->renderView('emails/verify_email.html.twig', [
                    'user' => $user,
                    'verificationUrl' => $verificationUrl,
                ]));

            $mailer->send($email);

            $this->addFlash('success', 'Inscription réussie ! Vérifiez votre boîte mail pour activer votre compte.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => $this->recaptchaSiteKey,
        ]);
    }

    private function verifyCaptcha(Request $request): bool
    {
        $token = $request->request->get('g-recaptcha-response');
        if (!$token) {
            return false;
        }

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $this->recaptchaSecretKey,
                    'response' => $token,
                    'remoteip' => $request->getClientIp(),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return ($data['success'] ?? false) && ($data['score'] ?? 0) >= 0.5;
        } catch (\Exception $e) {
            return false;
        }
    }
}