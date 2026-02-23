<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RulesController extends AbstractController
{
    #[Route('/regles-du-jeu', name: 'rules')]
    public function index(): Response
    {
        return $this->render('rules.html.twig', [
        ]);
    }
}