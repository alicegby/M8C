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
        return $this->render('admin/review/index.html.twig', [
            'pending' => $repo->findBy(['status' => 'pending'], ['createdAt' => 'DESC']),
            'approved' => $repo->findBy(['status' => 'approved'], ['createdAt' => 'DESC']),
            'rejected' => $repo->findBy(['status' => 'rejected'], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_review_approve', methods: ['POST'])]
    public function approve(Review $review, EntityManagerInterface $em): Response
    {
        $review->setStatus('approved');
        $review->setReviewedAt(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'Avis approuvé.');
        return $this->redirectToRoute('admin_review_index');
    }

    #[Route('/{id}/reject', name: 'admin_review_reject', methods: ['POST'])]
    public function reject(Review $review, EntityManagerInterface $em): Response
    {
        $review->setStatus('rejected');
        $review->setReviewedAt(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'Avis rejeté.');
        return $this->redirectToRoute('admin_review_index');
    }

    #[Route('/{id}/delete', name: 'admin_review_delete', methods: ['POST'])]
    public function delete(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_review_' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }
        return $this->redirectToRoute('admin_review_index');
    }
}