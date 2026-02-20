<?php

namespace App\Controller\Admin;

use App\Repository\ContactMessageRepository;
use App\Repository\MurderPartyRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(
        MurderPartyRepository $mpRepo,
        UserRepository $userRepo,
        ReviewRepository $reviewRepo,
        ContactMessageRepository $contactRepo,
        PurchaseRepository $purchaseRepo,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'total_mp' => $mpRepo->count([]),
            'total_users' => $userRepo->count(['isDeleted' => false]),
            'pending_reviews' => $reviewRepo->count(['status' => 'pending']),
            'unread_messages' => $contactRepo->count(['isRead' => false]),
            'total_revenue' => $purchaseRepo->getTotalRevenue(),
        ]);
    }
}