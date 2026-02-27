<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
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

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'];
        $this->supabaseKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY']; // ou ANON KEY si limité
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifie si email déjà utilisé
            $existing = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existing) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
                return $this->redirectToRoute('app_register');
            }

            $plainPassword = $form->get('plainPassword')->getData();

            // ── 1️⃣ Création utilisateur dans Supabase Auth ─────────
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

            // ── 2️⃣ Hash mot de passe et setup Symfony ─────────────
            $user->setPasswordHash($passwordHasher->hashPassword($user, $plainPassword));

            // Génère token de vérification email
            $token = bin2hex(random_bytes(32));
            $user->setEmailVerificationToken($token);
            $user->setIsVerified(false);

            $em->persist($user);
            $em->flush();

            // ── 3️⃣ Envoi email de vérification ────────────────────
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

        return $this->render('register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}