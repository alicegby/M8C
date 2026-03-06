<?php

namespace App\Controller\Admin;

use App\Repository\GameResultRepository;
use App\Repository\MurderPartyRepository;
use App\Repository\StatRepository;
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
        StatRepository $statRepo,
        MurderPartyRepository $mpRepo,
        GameResultRepository $resultRepo
    ): JsonResponse {
        try {
            $start = $request->query->get('start') ? new \DateTime($request->query->get('start')) : null;
            $end   = $request->query->get('end')   ? new \DateTime($request->query->get('end'))   : null;
            $mpIds = $request->query->all('mp');

            // On normalise les MP sélectionnés
            if (empty($mpIds)) $mpIds = [];

            // Liste des Murder Parties existantes
            $murderParties = array_map(fn($mp) => [
                'id'    => $mp->getId(),
                'title' => $mp->getTitle(),
            ], $mpRepo->findAll());

            // Statistiques sécurisées : fallback sur tableau vide ou 0 si pas de données
            return $this->json([
                'murderParties'       => $murderParties,
                'sales'               => $statRepo->getSalesByMP($mpIds ?: null, $start, $end),
                'success_rate'        => $statRepo->getSuccessRateByMurderParty($mpIds, $start, $end),
                'promo_vs_full'       => $statRepo->getPromoVsFullPrice($start, $end),
                'avg_basket'          => $statRepo->getAverageBasket($mpIds, $start, $end),
                'payment_methods'     => $statRepo->getPaymentMethodDistribution($start, $end),
                'returning_players'   => $statRepo->getReturningPlayersRate($start, $end),
                'rated_vs_sold'       => $resultRepo->getRatedVsSold($mpIds),
                'registrations'       => $statRepo->getRegistrationsByPeriod($start, $end),
                'source_distribution' => $statRepo->getSourceDistribution($start, $end),
            ]);
        } catch (\Throwable $e) {
            // Log ici si nécessaire pour debug
            return $this->json([
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'message' => 'Impossible de récupérer les stats, vérifie les collections ou les filtres.'
            ], 500);
        }
    }
}