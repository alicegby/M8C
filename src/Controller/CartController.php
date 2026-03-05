<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', ['_target_path' => '/panier']);
        }

        $cart = $this->cartService->getFullCartWithPromo($this->getUser());

        return $this->render('cart.html.twig', [
            'items' => $cart['items'],
            'total' => $cart['total'],
            'totalAfterDiscount' => $cart['totalAfterDiscount'],
            'promo' => $cart['promo'],
        ]);
    }

    #[Route('/ajouter/scenario/{slug}', name: 'add_scenario')]
    public function addScenario(string $slug): JsonResponse
    {
        $this->cartService->addScenario($slug);

        return new JsonResponse([
            'success' => true,
            'count' => $this->cartService->getCount(),
        ]);
    }

    #[Route('/ajouter/pack/{id}', name: 'add_pack')]
    public function addPack(string $id): JsonResponse
    {
        $this->cartService->addPack($id);

        return new JsonResponse([
            'success' => true,
            'count' => $this->cartService->getCount(),
        ]);
    }

    #[Route('/supprimer/{key}', name: 'remove')]
    public function remove(string $key): Response
    {
        $this->cartService->remove($key);
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/vider', name: 'clear')]
    public function clear(): Response
    {
        $this->cartService->clear();
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/promo/appliquer', name: 'apply_promo', methods: ['POST'])]
    public function applyPromo(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->request->get('code', '')));

        if (empty($code)) {
            return new JsonResponse(['success' => false, 'error' => 'Entre un code promo.']);
        }

        $result = $this->cartService->applyPromoCode($code, $this->getUser());

        return new JsonResponse($result);
    }

    #[Route('/promo/supprimer', name: 'remove_promo', methods: ['POST'])]
    public function removePromo(): JsonResponse
    {
        $this->cartService->removePromoCode();
        return new JsonResponse(['success' => true]);
    }
}