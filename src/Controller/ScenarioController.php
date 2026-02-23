<?php

namespace App\Controller;

use App\Repository\MurderPartyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScenarioController extends AbstractController
{
    #[Route('/scenarios', name: 'scenarios_list')]
    public function index(MurderPartyRepository $murderPartyRepository): Response
    {
        // On récupère tous les scénarios publiés
        $scenarios = $murderPartyRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('scenario.html.twig', [
            'scenarios' => $scenarios,
        ]);
    }

    #[Route('/scenario/{slug}', name: 'scenario_show')]
    public function show(string $slug, MurderPartyRepository $murderPartyRepository): Response
    {
        $scenario = $murderPartyRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$scenario) {
            throw $this->createNotFoundException('Scénario non trouvé');
        }

        return $this->render('scenario_show.html.twig', [
            'scenario' => $scenario,
        ]);
    }
}