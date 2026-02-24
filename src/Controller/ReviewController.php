<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReviewRepository;

class ReviewController extends AbstractController
{
    #[Route('/avis', name: 'avis')]
    public function list(ReviewRepository $reviewRepository): Response
    {
        // On récupère uniquement les avis approuvés, du plus récent au plus ancien
        $reviews = $reviewRepository->findBy(
            ['status' => 'approved'],
            ['createdAt' => 'DESC']
        );

        return $this->render('avis.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/avis/nouveau', name: 'avis_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setStatus('pending'); // on garde l'avis en attente
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre avis ! Il sera visible après validation.');
            return $this->redirectToRoute('avis');
        }

        return $this->render('avis_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}