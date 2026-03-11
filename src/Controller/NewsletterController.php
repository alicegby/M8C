<?php

namespace App\Controller;

use App\Entity\NewsletterSubscription;
use App\Service\BrevoService;
use App\Service\PromoCodeService;
use Symfony\Component\DependencyInjection\Attribute\Target;
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
    public function subscribe(
        Request $request,
        EntityManagerInterface $em,
        #[Target('brevo')] MailerInterface $mailer,
        PromoCodeService $promoCodeService,
        BrevoService $brevoService
    ): Response {
        $emailInput = $request->request->get('email');

        if (!$emailInput) {
            $this->addFlash('error', 'Veuillez renseigner un email.');
            return $this->redirectToRoute('app_home');
        }

        $existing = $em->getRepository(NewsletterSubscription::class)
                       ->findOneBy(['email' => $emailInput]);

        if ($existing) {
            $this->addFlash('info', 'Cet email est déjà inscrit.');
            return $this->redirectToRoute('app_home');
        }

        // Inscription en base
        $subscriber = new NewsletterSubscription();
        $subscriber->setEmail($emailInput);
        $subscriber->setUnsubscribeToken(bin2hex(random_bytes(16)));
        $em->persist($subscriber);
        $em->flush();

        // Synchronisation avec Brevo
        $brevoService->syncContact($emailInput);

        // Génère le code promo
        $promo = $promoCodeService->generateNewsletterCode();

        // Envoi email
        $email = (new TemplatedEmail())
            ->from('contact@meurtrehuisclos.fr')
            ->to($emailInput)
            ->subject('Bienvenue chez Meurtre à Huis Clos | Voici votre code promo')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'promoCode'        => $promo->getCode(),
                'app_url'          => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'unsubscribeToken' => $subscriber->getUnsubscribeToken(),
            ]);

        $mailer->send($email);

        $this->addFlash('success', 'Merci ! Votre email a été enregistré et votre code promo a été envoyé.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/newsletter/unsubscribe/{token}', name: 'newsletter_unsubscribe')]
    public function unsubscribe(
        string $token,
        EntityManagerInterface $em,
        BrevoService $brevoService
    ): Response {
        $subscriber = $em->getRepository(NewsletterSubscription::class)
                        ->findOneBy(['unsubscribeToken' => $token]);

        if (!$subscriber) {
            $this->addFlash('error', 'Lien de désinscription invalide.');
            return $this->redirectToRoute('app_home');
        }

        // Désabonnement dans Brevo
        $brevoService->unsubscribeContact($subscriber->getEmail());

        $em->remove($subscriber);
        $em->flush();

        $this->addFlash('success', 'Vous êtes désinscrit(e) de la newsletter.');
        return $this->redirectToRoute('app_home');
    }
}