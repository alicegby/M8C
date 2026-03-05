<?php

namespace App\Controller;

use App\Entity\Pack;
use App\Repository\PackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PackController extends AbstractController
{
    #[Route('/packs', name: 'packs_list')]
    public function index(PackRepository $packRepo): Response
    {
        $packs = $packRepo->findAll();

        return $this->render('pack.html.twig', [
            'packs' => $packs,
        ]);
    }

    #[Route('/packs/{id}', name: 'pack_show')]
    public function show(Pack $pack): Response
    {
        // Ici, $pack est injecté automatiquement grâce au ParamConverter
        return $this->render('pack_show.html.twig', [
            'pack' => $pack,
        ]);
    }
}