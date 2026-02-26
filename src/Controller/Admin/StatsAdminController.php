<?php

namespace App\Controller\Admin;

use App\Repository\GameResultRepository;
use App\Repository\MurderPartyRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class StatsAdminController extends AbstractController
{
    #[Route('', name: 'admin_stats')]
    public function index(): Response
    {
        // On n'envoie rien côté twig, tout sera chargé via AJAX
        return $this->render('admin/stats/index.html.twig');
    }

    #[Route('/data', name: 'admin_stats_data', methods: ['GET'])]
    public function data(
        PurchaseRepository $purchaseRepo,
        MurderPartyRepository $mpRepo,
        GameResultRepository $resultRepo
    ): JsonResponse {

        $murderParties = array_map(fn($mp) => [
            'id' => $mp->getId(),
            'title' => $mp->getTitle()
        ], $mpRepo->findAll());

        return $this->json([
           'murderParties' => $murderParties,
            'sales' => $purchaseRepo->getSalesByMP(), 
            'success_rate' => $resultRepo->getSuccessRateByMurderParty(),
            'promo_vs_full' => $purchaseRepo->getPromoVsFullPrice(),
            'avg_basket' => $purchaseRepo->getAverageBasket(),
            'payment_methods' => $purchaseRepo->getPaymentMethodDistribution(),
            'returning_players' => $purchaseRepo->getReturningPlayersRate(),
            'rated_vs_sold' => $resultRepo->getRatedVsSold(),
        ]);
    }
}