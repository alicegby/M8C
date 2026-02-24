<?php

namespace App\Controller;

use App\Repository\MurderPartyRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        MurderPartyRepository $murderPartyRepo,
        ReviewRepository $reviewRepo
    ): Response {
        // Récupérer les 5 scénarios publiés les mieux notés
        $topScenarios = $murderPartyRepo->findBy(
            ['isPublished' => true],
            ['averageRating' => 'DESC'],
            3
        );

        return $this->render('home.html.twig', [
            'topScenarios' => $topScenarios,
        ]);
    }
}