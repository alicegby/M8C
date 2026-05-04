<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class PagesController extends AbstractController
{
    private function getLastModified(string $template): string
    {
        $path = $this->getParameter('kernel.project_dir') . '/templates/' . $template;
        return (new \DateTime())->setTimestamp(filemtime($path))->format('d/m/Y');
    }

    #[Route('/mentions-legales', name: 'mentions')]
    public function mentionsLegales()
    {
        return $this->render('mentions.html.twig', [
            'last_modified' => $this->getLastModified('mentions.html.twig'),
        ]);
    }

    #[Route('/confidentialite', name: 'confidentialite')]
    public function confidentialite()
    {
        return $this->render('confidentialite.html.twig', [
            'last_modified' => $this->getLastModified('confidentialite.html.twig'),
        ]);
    }

    #[Route('/cgv', name: 'cgv')]
    public function cgv()
    {
        return $this->render('cgv.html.twig', [
            'last_modified' => $this->getLastModified('cgv.html.twig'),
        ]);
    }

    #[Route('/cgu', name: 'cgu')]
    public function cgu()
    {
        return $this->render('cgu.html.twig', [
            'last_modified' => $this->getLastModified('cgu.html.twig'),
        ]);
    }

    #[Route('/avertissement', name: 'avertissement')]
    public function avertissement()
    {
        return $this->render('avertissement.html.twig', [
            'last_modified' => $this->getLastModified('avertissement.html.twig'),
        ]);
    }
}