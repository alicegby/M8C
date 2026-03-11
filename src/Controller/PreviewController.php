<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PreviewController extends AbstractController
{
    #[Route('/preview/email/{template}', name: 'email_preview')]
    public function previewEmail(string $template): Response
    {
        return $this->render('emails/' . $template . '.html.twig', [
            'user' => (object)['prenom' => 'Alice'],
            'promoCode' => 'HELLO-ABC123',
            'app_url' => 'https://www.meurtrehuisclos.fr',
            'unsubscribeToken' => 'fake-token-123',
            'verificationUrl' => 'https://www.meurtrehuisclos.fr/verification',
            'resetUrl' => 'https://www.meurtrehuisclos.fr/reset',
            'data' => [
                'nom' => 'Gruby',
                'prenom' => 'Alice',
                'email' => 'alice@gmail.com',
                'sujet' => 'Test contact',
                'message' => 'Bonjour, ceci est un message de test.',
            ],
        ]);
    }
}