<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\MurderPartyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/paiement/{slug}', name: 'stripe_checkout')]
    public function checkout(
        string $slug,
        MurderPartyRepository $murderPartyRepository,
    ): Response {
        $scenario = $murderPartyRepository->findOneBy(['slug' => $slug]);

        if (!$scenario) {
            throw $this->createNotFoundException('Scénario introuvable');
        }

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $scenario->getTitle(),
                        'description' => substr($scenario->getSynopsis(), 0, 255),
                    ],
                    'unit_amount' => (int)($scenario->getPrice() * 100), // en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('stripe_success', [
                'slug' => $slug,
            ], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('scenario_show', [
                'slug' => $slug,
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'murder_party_id' => $scenario->getId(),
                'user_id' => $this->getUser()->getUserIdentifier(),
            ],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/paiement/succes/{slug}', name: 'stripe_success')]
    public function success(
        string $slug,
        Request $request,
        MurderPartyRepository $murderPartyRepository,
        EntityManagerInterface $em,
    ): Response {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('scenarios');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $stripeSession = Session::retrieve($sessionId);

        if ($stripeSession->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('scenarios');
        }

        $scenario = $murderPartyRepository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();

        if (!$scenario || !$user) {
            return $this->redirectToRoute('scenarios');
        }

        // Vérifie si déjà acheté
        $existing = $em->getRepository(Purchase::class)->findOneBy([
            'user' => $user,
            'murderParty' => $scenario,
        ]);

        if (!$existing) {
            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setMurderParty($scenario);
            $purchase->setPurchaseType('single');
            $purchase->setAmountPaid($scenario->getPrice());
            $purchase->setPaymentMethod($stripeSession->payment_method_types[0] ?? 'card');
            $purchase->setStripePaymentId($stripeSession->payment_intent);
            $purchase->setStatus('completed');

            $em->persist($purchase);
            $em->flush();
        }

        $this->addFlash('success', 'Achat confirmé ! Vous pouvez maintenant jouer à ' . $scenario->getTitle());
        return $this->redirectToRoute('scenario_show', ['slug' => $slug]);
    }

    #[Route('/paiement/annulation', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('info', 'Paiement annulé.');
        return $this->redirectToRoute('scenarios');
    }
}