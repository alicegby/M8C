<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DownloadController extends AbstractController
{
    #[Route('/telechargement', name: 'telechargement')]
    public function index(): Response
    {
        return $this->render('telechargement.html.twig', [
        ]);
    }
}