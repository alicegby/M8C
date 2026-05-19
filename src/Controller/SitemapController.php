<?php

namespace App\Controller;

use App\Repository\MurderPartyRepository;
use App\Repository\PackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap')]
    public function index(
        MurderPartyRepository $murderPartyRepo,
        PackRepository $packRepo
    ): Response {
        // Si tu as un champ "published"/"active" sur tes entités,
        // remplace findAll() par findBy(['published' => true])
        $murderParties = $murderPartyRepo->findAll();
        $packs         = $packRepo->findAll();

        $response = $this->render('sitemap.xml.twig', [
            'murderParties' => $murderParties,
            'packs'         => $packs,
        ]);

        $response->headers->set('Content-Type', 'application/xml');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}