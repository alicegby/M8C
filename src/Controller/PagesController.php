<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class PagesController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions')]
    public function mentionsLegales()
    {
        return $this->render('mentions.html.twig');
    }

    #[Route('/confidentialite', name: 'confidentialite')]
    public function confidentialite()
    {
        return $this->render('confidentialite.html.twig');
    }

    #[Route('/cgv', name: 'cgv')]
    public function cgv()
    {
        return $this->render('cgv.html.twig');
    }

    #[Route('/avertissement', name: 'avertissement')]
    public function avertissement()
    {
        return $this->render('avertissement.html.twig');
    }
}