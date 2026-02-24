<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Uuid;

class ForgotPasswordController extends AbstractController
{
    private MailerInterface $mailer;
    private EntityManagerInterface $em;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $em)
    {
        $this->mailer = $mailer;
        $this->em = $em;
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Génère un token unique
                $token = Uuid::v4()->toRfc4122(); // transforme en string
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $this->em->flush();

                // Envoie l'email
                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('meurtrehuisclos@gmail.com', 'Meurtre à Huis Clos'))
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'user' => $user,
                        'resetUrl' => $this->generateUrl('app_reset_password', ['token' => $token], 0),
                    ]);

                $this->mailer->send($emailMessage);
            }

            $this->addFlash('success', 'Si un compte existe avec cette adresse, un email de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('forgot_password.html.twig');
    }
}