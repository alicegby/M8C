<?php

namespace App\Controller\Admin;

use App\Repository\GameResultRepository;
use App\Repository\MurderPartyRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        return $this->render('admin/stats/index.html.twig');
    }

    #[Route('/data', name: 'admin_stats_data', methods: ['GET'])]
    public function data(
        Request $request,
        PurchaseRepository $purchaseRepo,
        MurderPartyRepository $mpRepo,
        GameResultRepository $resultRepo
    ): JsonResponse {

        // Récupération des filtres
        $start = $request->query->get('start') ? new \DateTime($request->query->get('start')) : null;
        $end = $request->query->get('end') ? new \DateTime($request->query->get('end')) : null;
        $mpIds = $request->query->all('mp') ?? [];

        $murderParties = array_map(fn($mp) => [
            'id' => $mp->getId(),
            'title' => $mp->getTitle()
        ], $mpRepo->findAll());

        return $this->json([
            'murderParties' => $murderParties,
            'sales' => $purchaseRepo->getSalesByMP($mpIds, $start, $end),
            'success_rate' => $resultRepo->getSuccessRateByMurderParty($mpIds, $start, $end),
            'promo_vs_full' => $purchaseRepo->getPromoVsFullPrice($start, $end),
            'avg_basket' => $purchaseRepo->getAverageBasket($mpIds, $start, $end),
            'payment_methods' => $purchaseRepo->getPaymentMethodDistribution($start, $end),
            'returning_players' => $purchaseRepo->getReturningPlayersRate($start, $end),
            'rated_vs_sold' => $resultRepo->getRatedVsSold($mpIds),
        ]);
    }
}