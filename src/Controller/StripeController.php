<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\MurderPartyRepository;
use App\Repository\PackRepository;
use App\Service\CartService;
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
    #[Route('/checkout/panier', name: 'stripe_checkout_cart')]
    public function checkoutCart(
        CartService $cartService,
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $cartService->getFullCart();

        if (empty($cart['items'])) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Construit les line_items pour Stripe
        $lineItems = [];
        foreach ($cart['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => (int)(floatval($item['price']) * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl(
                'stripe_success_cart',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl(
                'cart_index',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'metadata' => [
                'user_id' => $this->getUser()->getUserIdentifier(),
            ],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/checkout/success', name: 'stripe_success_cart')]
    public function successCart(
        Request $request,
        CartService $cartService,
        MurderPartyRepository $murderPartyRepository,
        PackRepository $packRepository,
        EntityManagerInterface $em,
    ): Response {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $stripeSession = Session::retrieve($sessionId);

        if ($stripeSession->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('cart_index');
        }

        $user = $this->getUser();
        $cart = $cartService->getFullCart();

        foreach ($cart['items'] as $item) {
            // Vérifie si déjà acheté
            $existing = null;

            if ($item['type'] === 'scenario') {
                $existing = $em->getRepository(Purchase::class)->findOneBy([
                    'user' => $user,
                    'murderParty' => $item['entity'],
                ]);
            } elseif ($item['type'] === 'pack') {
                $existing = $em->getRepository(Purchase::class)->findOneBy([
                    'user' => $user,
                    'pack' => $item['entity'],
                ]);
            }

            if ($existing) continue;

            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setAmountPaid($item['price']);
            $purchase->setPaymentMethod('card');
            $purchase->setStripePaymentId($stripeSession->payment_intent ?? $sessionId);
            $purchase->setStatus('completed');

            if ($item['type'] === 'scenario') {
                $purchase->setMurderParty($item['entity']);
                $purchase->setPurchaseType('single');
            } elseif ($item['type'] === 'pack') {
                $purchase->setPack($item['entity']);
                $purchase->setPurchaseType('pack');

                // Débloque aussi chaque scénario du pack
                foreach ($item['entity']->getMurderParties() as $mp) {
                    $existingMp = $em->getRepository(Purchase::class)->findOneBy([
                        'user' => $user,
                        'murderParty' => $mp,
                    ]);
                    if (!$existingMp) {
                        $mpPurchase = new Purchase();
                        $mpPurchase->setUser($user);
                        $mpPurchase->setMurderParty($mp);
                        $mpPurchase->setAmountPaid('0.00');
                        $mpPurchase->setPaymentMethod('card');
                        $mpPurchase->setStripePaymentId($stripeSession->payment_intent ?? $sessionId);
                        $mpPurchase->setStatus('completed');
                        $mpPurchase->setPurchaseType('single');
                        $em->persist($mpPurchase);
                    }
                }
            }

            $em->persist($purchase);
        }

        $em->flush();
        $cartService->clear();

        $this->addFlash('success', 'Paiement confirmé ! Vos Murder Parties sont disponibles.');
        return $this->redirectToRoute('app_account');
    }
}