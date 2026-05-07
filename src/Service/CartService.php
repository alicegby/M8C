<?php

namespace App\Service;

use App\Repository\MurderPartyRepository;
use App\Repository\PackRepository;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUsageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private MurderPartyRepository $murderPartyRepository,
        private PackRepository $packRepository,
        private PromoCodeRepository $promoCodeRepository,
        private PromoCodeUsageRepository $promoCodeUsageRepository,
    ) {}

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->getSession()->get('cart', []);
    }

    public function addScenario(string $slug): void
    {
        $cart = $this->getCart();
        $key = 'scenario_' . $slug;
        if (!isset($cart[$key])) {
            $cart[$key] = ['type' => 'scenario', 'slug' => $slug];
        }
        $this->getSession()->set('cart', $cart);
    }

    public function addPack(string $id): void
    {
        $cart = $this->getCart();
        $key = 'pack_' . $id;
        if (!isset($cart[$key])) {
            $cart[$key] = ['type' => 'pack', 'id' => $id];
        }
        $this->getSession()->set('cart', $cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->getSession()->set('cart', $cart);
    }

    public function clear(): void
    {
        $this->getSession()->set('cart', []);
    }

    public function getCount(): int
    {
        return count($this->getCart());
    }

    public function getFullCart(): array
    {
        $cart = $this->getCart();
        $items = [];
        $total = 0;

        foreach ($cart as $key => $item) {
            if ($item['type'] === 'scenario') {
                $scenario = $this->murderPartyRepository->findOneBy(['slug' => $item['slug']]);
                if ($scenario) {
                    $items[] = [
                        'key' => $key,
                        'type' => 'scenario',
                        'entity' => $scenario,
                        'name' => $scenario->getTitle(),
                        'price' => $scenario->getPrice(),
                    ];
                    $total += (float) $scenario->getPrice();
                }
            } elseif ($item['type'] === 'pack') {
                $pack = $this->packRepository->find($item['id']);
                if ($pack) {
                    $items[] = [
                        'key' => $key,
                        'type' => 'pack',
                        'entity' => $pack,
                        'name' => $pack->getName(),
                        'price' => $pack->getPrice(),
                    ];
                    $total += (float) $pack->getPrice();
                }
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    public function applyPromoCode(string $code, ?object $user): array
    {
        $promoCode = $this->promoCodeRepository->findOneBy([
            'code'     => strtoupper($code),
            'isActive' => true,
        ]);

        if (!$promoCode) {
            return ['success' => false, 'error' => 'Code promo invalide ou inactif.'];
        }

        $now = new \DateTime();

        // Vérifie la date de début
        if ($promoCode->getValidFrom() && $now < $promoCode->getValidFrom()) {
            return ['success' => false, 'error' => 'Ce code promo n\'est pas encore actif.'];
        }

        // Vérifie la date de fin — et désactive automatiquement si expiré
        if ($promoCode->getValidUntil() && $now > $promoCode->getValidUntil()) {
            $promoCode->setIsActive(false);
            $this->promoCodeRepository->save($promoCode, true); // ou $em->flush()
            return ['success' => false, 'error' => 'Ce code promo a expiré.'];
        }

        // Vérifie le nombre max d'utilisations global
        if ($promoCode->getMaxUses() !== null && $promoCode->getCurrentUses() >= $promoCode->getMaxUses()) {
            return ['success' => false, 'error' => 'Ce code promo a atteint sa limite d\'utilisation.'];
        }

        if ($user) {
            // Vérifie si l'utilisateur l'a déjà utilisé (unicité via PromoCodeUsage)
            $alreadyUsed = $this->promoCodeUsageRepository->findOneBy([
                'user'      => $user,
                'promoCode' => $promoCode,
            ]);
            if ($alreadyUsed) {
                return ['success' => false, 'error' => 'Vous avez déjà utilisé ce code promo.'];
            }

            // Vérifie la fenêtre de 30 jours après inscription si le code est de type "welcome"
            if ($promoCode->isWelcomeCode()) {
                $registeredAt = \DateTime::createFromInterface($user->getCreatedAt());
                $limit = (clone $registeredAt)->modify('+30 days');
                if ($now > $limit) {
                    return ['success' => false, 'error' => 'Ce code promo est réservé aux nouveaux inscrits (30 jours après inscription).'];
                }
            }
        }

        $this->getSession()->set('promo_code', $promoCode->getCode());

        return [
            'success'       => true,
            'code'          => $promoCode->getCode(),
            'discountType'  => $promoCode->getDiscountType(),
            'discountValue' => $promoCode->getDiscountValue(),
        ];
    }

public function getAppliedPromoCode(): ?string
{
    return $this->getSession()->get('promo_code');
}

public function removePromoCode(): void
{
    $this->getSession()->remove('promo_code');
}

    public function getFullCartWithPromo(?object $user): array
    {
        $cart = $this->getFullCart();
        $promoCodeStr = $this->getAppliedPromoCode();
        $discount = 0;
        $promoData = null;

        if ($promoCodeStr) {
            $promoCode = $this->promoCodeRepository->findOneBy(['code' => $promoCodeStr]);
            if ($promoCode) {
                if ($promoCode->getDiscountType() === 'percentage') {
                    $discount = $cart['total'] * (floatval($promoCode->getDiscountValue()) / 100);
                } else {
                    $discount = floatval($promoCode->getDiscountValue());
                }
                $discount = min($discount, $cart['total']);
                $promoData = [
                    'code' => $promoCode->getCode(),
                    'discountType' => $promoCode->getDiscountType(),
                    'discountValue' => $promoCode->getDiscountValue(),
                    'discount' => round($discount, 2),
                ];
            }
        }

        return [
            'items' => $cart['items'],
            'total' => $cart['total'],
            'totalAfterDiscount' => round($cart['total'] - $discount, 2),
            'promo' => $promoData,
        ];
    }
}