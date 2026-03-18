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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    #[Route('/webhook/stripe/', methods: ['POST'])]
    public function stripeWebhook(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        MurderPartyRepository $murderPartyRepository,
        PackRepository $packRepository,
        StatService $statService,
        MailerInterface $mailer
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

        // ─── CHECKOUT COMPLÉTÉ ────────────────────────────────────────────────────
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
                        'user'        => $user,
                        'murderParty' => $mp,
                        'status'      => 'completed',
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
                        'user'   => $user,
                        'pack'   => $pack,
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
                                'user'        => $user,
                                'murderParty' => $mp,
                                'status'      => 'completed',
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
                            }
                        }
                    }
                }
            }

            // Gestion code promo
            $promoCodeStr = $session->metadata->promo_code ?? null;
            if ($promoCodeStr) {
                $promoCode = $em->getRepository(\App\Entity\PromoCode::class)->findOneBy([
                    'code' => $promoCodeStr,
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

            $mailer->send(
                (new TemplatedEmail())
                    ->from('contact@meurtrehuisclos.fr')
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre achat')
                    ->htmlTemplate('emails/purchase_confirmation.html.twig')
                    ->context(['user' => $user, 'session' => $session])
            );
        }

        // ─── REMBOURSEMENT ────────────────────────────────────────────────────────
        if ($event->type === 'charge.refunded') {
            $charge = $event->data->object;
            $paymentIntentId = $charge->payment_intent;

            if (!$paymentIntentId) {
                return new Response('Pas de payment_intent', 200);
            }

            $purchase = $em->getRepository(Purchase::class)->findOneBy([
                'stripePaymentId' => $paymentIntentId,
            ]);

            if (!$purchase || $purchase->getStatus() === 'refunded') {
                return new Response('Purchase introuvable ou déjà remboursé', 200);
            }

            // Supprime les UserMurderParty liés
            $umps = $em->getRepository(\App\Entity\UserMurderParty::class)->findBy([
                'purchase' => $purchase,
            ]);
            foreach ($umps as $ump) {
                $em->remove($ump);
            }

            // Si c'est un pack, supprime aussi les UMP des scénarios offerts
            if ($purchase->getPack()) {
                foreach ($purchase->getPack()->getMurderParties() as $mp) {
                    $mpPurchases = $em->getRepository(Purchase::class)->findBy([
                        'user'        => $purchase->getUser(),
                        'murderParty' => $mp,
                        'amountPaid'  => '0.00',
                    ]);
                    foreach ($mpPurchases as $mpPurchase) {
                        $umps2 = $em->getRepository(\App\Entity\UserMurderParty::class)->findBy([
                            'purchase' => $mpPurchase,
                        ]);
                        foreach ($umps2 as $ump) {
                            $em->remove($ump);
                        }
                        $em->remove($mpPurchase);
                    }
                }
            }

            $purchase->setStatus('refunded');
            $em->flush();

            $mailer->send(
                (new TemplatedEmail())
                    ->from('contact@meurtrehuisclos.fr')
                    ->to($purchase->getUser()->getEmail())
                    ->subject('Votre remboursement a été effectué')
                    ->htmlTemplate('emails/refund_confirmation.html.twig')
                    ->context(['purchase' => $purchase, 'user' => $purchase->getUser()])
            );
        }

        return new Response('OK', 200);
    }
}