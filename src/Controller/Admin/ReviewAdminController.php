<?php

namespace App\Controller\Admin; 

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reviews')]
#[IsGranted('ROLE_ADMIN')]
class ReviewAdminController extends AbstractController
{
    #[Route('', name: 'admin_review_index')]
    public function index(ReviewRepository $repo): Response
    {
        return $this->render('admin/avis/index.html.twig', [
            'pending' => $repo->findBy(['status' => 'pending'], ['createdAt' => 'DESC']),
            'approved' => $repo->findBy(['status' => 'approved'], ['createdAt' => 'DESC']),
            'rejected' => $repo->findBy(['status' => 'rejected'], ['createdAt' => 'DESC']),
        ]);
    }
    #[Route('/{id}/edit', name: 'admin_review_edit', methods: ['GET','POST'])]
    public function edit(Review $review, Request $request, EntityManagerInterface $em, ReviewRepository $repo): Response
    {
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $review->setStatus('approved');
            } elseif ($action === 'reject') {
                $review->setStatus('rejected');
            }

            $review->setReviewedAt(new \DateTime());
            $em->flush();

            // Si c'est une requête AJAX, on renvoie directement le HTML de la liste
            if ($request->isXmlHttpRequest()) {
                return $this->render('admin/avis/index.html.twig', [
                    'pending' => $repo->findBy(['status' => 'pending'], ['createdAt' => 'DESC']),
                    'approved' => $repo->findBy(['status' => 'approved'], ['createdAt' => 'DESC']),
                    'rejected' => $repo->findBy(['status' => 'rejected'], ['createdAt' => 'DESC']),
                ]);
            }

            // Sinon on fait une redirection classique
            return $this->redirectToRoute('admin_dashboard');
        }

        // GET → afficher la page edit
        return $this->render('admin/avis/edit.html.twig', [
            'review' => $review,
        ]);
    }
}