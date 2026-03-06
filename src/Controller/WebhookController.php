<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\MurderPartyRepository;
use App\Repository\PackRepository;
use App\Repository\UserRepository;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        MurderPartyRepository $murderPartyRepository,
        PackRepository $packRepository,
        StatService $statService,
    ): Response {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $_ENV['STRIPE_WEBHOOK_SECRET']
            );
        } catch (\Exception $e) {
            return new Response('Signature invalide', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status !== 'paid') {
                return new Response('Non payé', 200);
            }

            $userEmail = $session->metadata->user_id ?? null;
            if (!$userEmail) {
                return new Response('User manquant', 200);
            }

            $user = $userRepository->findOneBy(['email' => $userEmail]);
            if (!$user) {
                return new Response('User introuvable', 200);
            }

            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
            $lineItems = Session::allLineItems($session->id);

            foreach ($lineItems->data as $lineItem) {
                $productName = $lineItem->description;

                // Cherche si c'est un scénario
                $mp = $murderPartyRepository->findOneBy(['title' => $productName]);
                if ($mp) {
                    $existing = $em->getRepository(Purchase::class)->findOneBy([
                        'user' => $user,
                        'murderParty' => $mp,
                        'status' => 'completed',
                    ]);
                    if (!$existing) {
                        $purchase = new Purchase();
                        $purchase->setUser($user);
                        $purchase->setMurderParty($mp);
                        $purchase->setPurchaseType('single');
                        $purchase->setAmountPaid((string)($lineItem->amount_total / 100));
                        $purchase->setPaymentMethod('card');
                        $purchase->setStripePaymentId($session->payment_intent ?? $session->id);
                        $purchase->setStatus('completed');
                        $purchase->setSource('web');
                        $em->persist($purchase);
                        $em->flush();

                        $statService->recordPurchase($purchase, 'web');
                    }
                    continue;
                }

                // Cherche si c'est un pack
                $pack = $packRepository->findOneBy(['name' => $productName]);
                if ($pack) {
                    $existing = $em->getRepository(Purchase::class)->findOneBy([
                        'user' => $user,
                        'pack' => $pack,
                        'status' => 'completed',
                    ]);
                    if (!$existing) {
                        $purchase = new Purchase();
                        $purchase->setUser($user);
                        $purchase->setPack($pack);
                        $purchase->setPurchaseType('pack');
                        $purchase->setAmountPaid((string)($lineItem->amount_total / 100));
                        $purchase->setPaymentMethod('card');
                        $purchase->setStripePaymentId($session->payment_intent ?? $session->id);
                        $purchase->setStatus('completed');
                        $purchase->setSource('web');
                        $em->persist($purchase);
                        $em->flush();

                        $statService->recordPurchase($purchase, 'web');

                        // Débloque aussi chaque scénario du pack
                        foreach ($pack->getMurderParties() as $mp) {
                            $existingMp = $em->getRepository(Purchase::class)->findOneBy([
                                'user' => $user,
                                'murderParty' => $mp,
                                'status' => 'completed',
                            ]);
                            if (!$existingMp) {
                                $mpPurchase = new Purchase();
                                $mpPurchase->setUser($user);
                                $mpPurchase->setMurderParty($mp);
                                $mpPurchase->setPurchaseType('single');
                                $mpPurchase->setAmountPaid('0.00');
                                $mpPurchase->setPaymentMethod('card');
                                $mpPurchase->setStripePaymentId($session->payment_intent ?? $session->id);
                                $mpPurchase->setStatus('completed');
                                $em->persist($mpPurchase);
                                $em->flush();
                                // On n'enregistre pas de stat pour les MPs offerts dans un pack
                            }
                        }
                    }
                }
            }

            // Gestion code promo
            $promoCodeStr = $session->metadata->promo_code ?? null;
            if ($promoCodeStr) {
                $promoCode = $em->getRepository(\App\Entity\PromoCode::class)->findOneBy([
                    'code' => $promoCodeStr
                ]);
                if ($promoCode) {
                    $promoCode->setCurrentUses($promoCode->getCurrentUses() + 1);

                    $usage = new \App\Entity\PromoCodeUsage();
                    $usage->setUser($user);
                    $usage->setPromoCode($promoCode);
                    $em->persist($usage);
                    $em->flush();
                }
            }
        }

        return new Response('OK', 200);
    }
}