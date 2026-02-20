<?php

namespace App\Controller\Admin;

use App\Entity\Character;
use App\Entity\Clue;
use App\Entity\MurderParty;
use App\Form\CharacterType;
use App\Form\ClueType;
use App\Form\MurderPartyType;
use App\Repository\MurderPartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/murder-parties')]
#[IsGranted('ROLE_ADMIN')]
class MurderPartyAdminController extends AbstractController
{
    #[Route('', name: 'admin_mp_index')]
    public function index(MurderPartyRepository $repo): Response
    {
        return $this->render('admin/murder_party/index.html.twig', [
            'murder_parties' => $repo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_mp_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $mp = new MurderParty();
        $form = $this->createForm(MurderPartyType::class, $mp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($mp);
            $em->flush();
            $this->addFlash('success', 'Murder Party créée avec succès.');
            return $this->redirectToRoute('admin_mp_edit', ['id' => $mp->getId()]);
        }

        return $this->render('admin/murder_party/form.html.twig', [
            'form' => $form,
            'mp' => $mp,
            'title' => 'Nouvelle Murder Party',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_mp_edit')]
    public function edit(MurderParty $mp, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MurderPartyType::class, $mp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Murder Party mise à jour.');
            return $this->redirectToRoute('admin_mp_edit', ['id' => $mp->getId()]);
        }

        // Formulaire ajout personnage
        $character = new Character();
        $characterForm = $this->createForm(CharacterType::class, $character);
        $characterForm->handleRequest($request);

        if ($characterForm->isSubmitted() && $characterForm->isValid()) {
            $character->setMurderParty($mp);
            $em->persist($character);
            $em->flush();
            $this->addFlash('success', 'Personnage ajouté.');
            return $this->redirectToRoute('admin_mp_edit', ['id' => $mp->getId()]);
        }

        // Formulaire ajout indice
        $clue = new Clue();
        $clueForm = $this->createForm(ClueType::class, $clue, ['murder_party' => $mp]);
        $clueForm->handleRequest($request);

        if ($clueForm->isSubmitted() && $clueForm->isValid()) {
            $clue->setMurderParty($mp);
            $em->persist($clue);
            $em->flush();
            $this->addFlash('success', 'Indice ajouté.');
            return $this->redirectToRoute('admin_mp_edit', ['id' => $mp->getId()]);
        }

        return $this->render('admin/murder_party/form.html.twig', [
            'form' => $form,
            'character_form' => $characterForm,
            'clue_form' => $clueForm,
            'mp' => $mp,
            'title' => 'Modifier : ' . $mp->getTitle(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_mp_delete', methods: ['POST'])]
    public function delete(MurderParty $mp, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_mp_' . $mp->getId(), $request->request->get('_token'))) {
            $em->remove($mp);
            $em->flush();
            $this->addFlash('success', 'Murder Party supprimée.');
        }
        return $this->redirectToRoute('admin_mp_index');
    }

    #[Route('/character/{id}/delete', name: 'admin_character_delete', methods: ['POST'])]
    public function deleteCharacter(Character $character, Request $request, EntityManagerInterface $em): Response
    {
        $mpId = $character->getMurderParty()->getId();
        if ($this->isCsrfTokenValid('delete_char_' . $character->getId(), $request->request->get('_token'))) {
            $em->remove($character);
            $em->flush();
            $this->addFlash('success', 'Personnage supprimé.');
        }
        return $this->redirectToRoute('admin_mp_edit', ['id' => $mpId]);
    }

    #[Route('/clue/{id}/delete', name: 'admin_clue_delete', methods: ['POST'])]
    public function deleteClue(Clue $clue, Request $request, EntityManagerInterface $em): Response
    {
        $mpId = $clue->getMurderParty()->getId();
        if ($this->isCsrfTokenValid('delete_clue_' . $clue->getId(), $request->request->get('_token'))) {
            $em->remove($clue);
            $em->flush();
            $this->addFlash('success', 'Indice supprimé.');
        }
        return $this->redirectToRoute('admin_mp_edit', ['id' => $mpId]);
    }
}