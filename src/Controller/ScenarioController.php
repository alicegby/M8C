<?php

namespace App\Controller;

use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MurderPartyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScenarioController extends AbstractController
{
    #[Route('/scenarios', name: 'scenarios_list')]
    public function index(MurderPartyRepository $murderPartyRepository, EntityManagerInterface $em): Response
    {
        // On récupère tous les scénarios publiés
        $scenarios = $murderPartyRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC']
        );

        $purchasedIds = [];
        if ($this->getUser()) {
            $umps = $em->getRepository(\App\Entity\UserMurderParty::class)->findBy([
                'user' => $this->getUser(),
            ]);
            foreach ($umps as $ump) {
                $purchasedIds[] = $ump->getMurderParty()->getId();
            }
        }

        return $this->render('scenario.html.twig', [
            'scenarios'    => $scenarios,
            'purchasedIds' => $purchasedIds,
        ]);
    }

    #[Route('/scenario/{slug}', name: 'scenario_show')]
    public function show(
        string $slug,
        MurderPartyRepository $murderPartyRepository,
        EntityManagerInterface $em,
    ): Response {
        $scenario = $murderPartyRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$scenario) {
            throw $this->createNotFoundException('Scénario non trouvé');
        }

        $isPurchased = false;
        if ($this->getUser()) {
            $isPurchased = $em->getRepository(Purchase::class)->findOneBy([
                'user' => $this->getUser(),
                'murderParty' => $scenario,
                'status' => 'completed',
            ]) !== null;
        }

        return $this->render('scenario_show.html.twig', [
            'scenario' => $scenario,
            'isPurchased' => $isPurchased,
        ]);
    }
}