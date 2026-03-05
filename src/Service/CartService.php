<?php

namespace App\Service;

use App\Repository\MurderPartyRepository;
use App\Repository\PackRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private MurderPartyRepository $murderPartyRepository,
        private PackRepository $packRepository,
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
}