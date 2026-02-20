<?php

namespace App\Controller\Admin;

use App\Entity\PromoCode;
use App\Form\PromoCodeType;
use App\Repository\PromoCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/promo-codes')]
#[IsGranted('ROLE_ADMIN')]
class PromoCodeAdminController extends AbstractController
{
    #[Route('', name: 'admin_promo_index')]
    public function index(PromoCodeRepository $repo): Response
    {
        return $this->render('admin/promo_code/index.html.twig', [
            'promo_codes' => $repo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_promo_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $promo = new PromoCode();
        $form = $this->createForm(PromoCodeType::class, $promo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($promo);
            $em->flush();
            $this->addFlash('success', 'Code promo créé.');
            return $this->redirectToRoute('admin_promo_index');
        }

        return $this->render('admin/promo_code/form.html.twig', [
            'form' => $form,
            'title' => 'Nouveau code promo',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_promo_edit')]
    public function edit(PromoCode $promo, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PromoCodeType::class, $promo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Code promo mis à jour.');
            return $this->redirectToRoute('admin_promo_index');
        }

        return $this->render('admin/promo_code/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier : ' . $promo->getCode(),
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_promo_toggle', methods: ['POST'])]
    public function toggle(PromoCode $promo, EntityManagerInterface $em): Response
    {
        $promo->setIsActive(!$promo->isActive());
        $em->flush();
        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('admin_promo_index');
    }

    #[Route('/{id}/delete', name: 'admin_promo_delete', methods: ['POST'])]
    public function delete(PromoCode $promo, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_promo_' . $promo->getId(), $request->request->get('_token'))) {
            $em->remove($promo);
            $em->flush();
            $this->addFlash('success', 'Code promo supprimé.');
        }
        return $this->redirectToRoute('admin_promo_index');
    }
}