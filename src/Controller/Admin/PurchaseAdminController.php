<?php

namespace App\Controller\Admin;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Refund;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/achats')]
#[IsGranted('ROLE_ADMIN')]
class PurchaseAdminController extends AbstractController
{
    #[Route('', name: 'admin_purchase_index')]
    public function index(PurchaseRepository $purchaseRepo): Response
    {
        $purchases = $purchaseRepo->findBy(
            [],
            ['purchasedAt' => 'DESC']
        );

        return $this->render('admin/purchase/index.html.twig', [
            'purchases' => $purchases,
        ], );
    }

    #[Route('/rembourser/{id}', name: 'admin_purchase_refund', methods: ['POST'])]
    public function refund(
        Purchase $purchase,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        // Vérifie le token CSRF
        if (!$this->isCsrfTokenValid('refund_' . $purchase->getId(), $request->request->get('_token'))) {
            return $this->json(['success' => false, 'error' => 'Token invalide'], 403);
        }

        // Vérifie que le remboursement est possible
        if ($purchase->getStatus() === 'refunded') {
            return $this->json(['success' => false, 'error' => 'Déjà remboursé'], 400);
        }

        if (!$purchase->isRefundable()) {
            return $this->json(['success' => false, 'error' => 'Délai de rétractation dépassé ou partie déjà jouée'], 400);
        }

        if (!$purchase->getStripePaymentId()) {
            return $this->json(['success' => false, 'error' => 'Aucun ID de paiement Stripe'], 400);
        }

        try {
            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            Refund::create([
                'payment_intent' => $purchase->getStripePaymentId(),
                'amount' => (int)(floatval($purchase->getAmountPaid()) * 100),
            ]);

            $purchase->setStatus('refunded');
            $em->flush();

            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}