<?php

namespace App\Controller;

use App\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'newsletter_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $emailInput = $request->request->get('email');

        if (!$emailInput) {
            $this->addFlash('error', 'Veuillez renseigner un email.');
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si l'email existe déjà
        $existing = $em->getRepository(NewsletterSubscription::class)
                       ->findOneBy(['email' => $emailInput]);

        if ($existing) {
            $this->addFlash('info', 'Cet email est déjà inscrit.');
            return $this->redirectToRoute('app_home');
        }

        // Crée le subscriber avec token unique
        $subscriber = new NewsletterSubscription();
        $subscriber->setEmail($emailInput);
        $subscriber->setUnsubscribeToken(bin2hex(random_bytes(16))); // 32 caractères hex
        $em->persist($subscriber);
        $em->flush();

        // Génère un code promo
        $promoCode = strtoupper(bin2hex(random_bytes(3))); // ex: 6 caractères

        // Envoie l'email avec le code promo
        $email = (new TemplatedEmail())
            ->from('meurtrehuisclos@gmail.com')
            ->to($emailInput)
            ->subject('Bienvenue chez Meurtre à Huis Clos | Voici votre code promo')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'promoCode' => $promoCode,
                'app_url' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'unsubscribeToken' => $subscriber->getUnsubscribeToken(),
            ]);

        $mailer->send($email);

        $this->addFlash('success', 'Merci ! Votre email a été enregistré et votre code promo a été envoyé.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/newsletter/unsubscribe/{token}', name: 'newsletter_unsubscribe')]
    public function unsubscribe(string $token, EntityManagerInterface $em): Response
    {
        $subscriber = $em->getRepository(NewsletterSubscription::class)
                        ->findOneBy(['unsubscribeToken' => $token]);

        if (!$subscriber) {
            $this->addFlash('error', 'Lien de désinscription invalide.');
            return $this->redirectToRoute('app_home');
        }

        $em->remove($subscriber);
        $em->flush();

        $this->addFlash('success', 'Vous êtes désinscrit(e) de la newsletter.');
        return $this->redirectToRoute('app_home');
    }
}