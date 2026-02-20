<?php

namespace App\Controller\Admin;

use App\Entity\Pack;
use App\Form\PackType;
use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/packs')]
#[IsGranted('ROLE_ADMIN')]
class PackAdminController extends AbstractController
{
    #[Route('', name: 'admin_pack_index')]
    public function index(PackRepository $repo): Response
    {
        return $this->render('admin/pack/index.html.twig', [
            'packs' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_pack_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pack);
            $em->flush();
            $this->addFlash('success', 'Pack créé avec succès.');
            return $this->redirectToRoute('admin_pack_index');
        }

        return $this->render('admin/pack/form.html.twig', [
            'form' => $form,
            'title' => 'Nouveau pack',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_pack_edit')]
    public function edit(Pack $pack, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Pack mis à jour.');
            return $this->redirectToRoute('admin_pack_index');
        }

        return $this->render('admin/pack/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier : ' . $pack->getName(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_pack_delete', methods: ['POST'])]
    public function delete(Pack $pack, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_pack_' . $pack->getId(), $request->request->get('_token'))) {
            $em->remove($pack);
            $em->flush();
            $this->addFlash('success', 'Pack supprimé.');
        }
        return $this->redirectToRoute('admin_pack_index');
    }
}