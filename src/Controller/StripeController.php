<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\PromoCodeRepository;
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
        PromoCodeRepository $promoCodeRepository,
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $cartService->getFullCartWithPromo($this->getUser());

        if (empty($cart['items'])) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

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

        // Ajoute une ligne de réduction si code promo appliqué
        if ($cart['promo'] && $cart['promo']['discount'] > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Code promo : ' . $cart['promo']['code'],
                    ],
                    'unit_amount' => -(int)($cart['promo']['discount'] * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Métadonnées pour le webhook
        $metadata = [
            'user_id' => $this->getUser()->getUserIdentifier(),
        ];
        if ($cart['promo']) {
            $metadata['promo_code'] = $cart['promo']['code'];
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
            'metadata' => $metadata,
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/checkout/success', name: 'stripe_success_cart')]
    public function successCart(
        Request $request,
        CartService $cartService,
    ): Response {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId || !$this->getUser()) {
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        try {
            $stripeSession = Session::retrieve($sessionId);
        } catch (\Exception $e) {
            return $this->redirectToRoute('cart_index');
        }

        if ($stripeSession->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('cart_index');
        }

        $cart = $cartService->getFullCartWithPromo($this->getUser());
        $items = $cart['items'];
        $total = $cart['total'];
        $totalAfterDiscount = $cart['totalAfterDiscount'];
        $promo = $cart['promo'];

        $cartService->clear();
        $cartService->removePromoCode();

        return $this->render('payment_success.html.twig', [
            'items' => $items,
            'total' => $total,
            'totalAfterDiscount' => $totalAfterDiscount,
            'promo' => $promo,
        ]);
    }
}