<?php

namespace App\Controller\Admin;

use App\Repository\GameResultRepository;
use App\Repository\MurderPartyRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class StatsAdminController extends AbstractController
{
    #[Route('', name: 'admin_stats')]
    public function index(
        PurchaseRepository $purchaseRepo,
        MurderPartyRepository $mpRepo,
        GameResultRepository $resultRepo,
    ): Response {
        return $this->render('admin/stats/index.html.twig', [
            'revenue_by_month' => $purchaseRepo->getRevenueByMonth(),
            'best_selling' => $purchaseRepo->getBestSellingMurderParties(),
            'most_played' => $mpRepo->getMostPlayed(),
            'success_rate' => $resultRepo->getSuccessRateByMurderParty(),
        ]);
    }
}