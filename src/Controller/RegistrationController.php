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

class RegistrationController extends AbstractController
{
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
            // Vérifie email déjà existant
            $existing = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existing) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
                return $this->redirectToRoute('app_register');
            }

            // Hash mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPasswordHash($passwordHasher->hashPassword($user, $plainPassword));

            // Génère token de vérification
            $token = bin2hex(random_bytes(32));
            $user->setEmailVerificationToken($token);
            $user->setIsVerified(false);

            $em->persist($user);
            $em->flush();

            // Envoie email de vérification
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