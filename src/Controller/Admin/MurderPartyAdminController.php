<?php

namespace App\Controller\Admin;

use App\Entity\MurderParty;
use App\Entity\Character;
use App\Entity\Clue;
use App\Form\MurderPartyType;
use App\Form\GamePlayer;
use App\Repository\MurderPartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin/murder-parties')]
#[IsGranted('ROLE_ADMIN')]
class MurderPartyAdminController extends AbstractController
{
    #[Route('', name: 'admin_mp_index')]
    public function index(MurderPartyRepository $repo, Request $request): Response
    {
        $qb = $repo->createQueryBuilder('mp');

        $keyword = $request->query->get('keyword', '');
        $minPlayers = $request->query->get('minPlayers', '');
        $maxPlayers = $request->query->get('maxPlayers', '');
        $minDuration = $request->query->get('minDuration', '');
        $maxDuration = $request->query->get('maxDuration', '');

        if ($keyword) {
            $qb->andWhere('mp.title LIKE :keyword OR mp.synopsis LIKE :keyword')
               ->setParameter('keyword', "%$keyword%");
        }
        if ($minPlayers) $qb->andWhere('mp.nbPlayers >= :minPlayers')->setParameter('minPlayers', $minPlayers);
        if ($maxPlayers) $qb->andWhere('mp.nbPlayers <= :maxPlayers')->setParameter('maxPlayers', $maxPlayers);
        if ($minDuration) $qb->andWhere('mp.duree >= :minDuration')->setParameter('minDuration', $minDuration);
        if ($maxDuration) $qb->andWhere('mp.duree <= :maxDuration')->setParameter('maxDuration', $maxDuration);

        $murderParties = $qb->orderBy('mp.createdAt', 'DESC')->getQuery()->getResult();

        return $this->render('admin/mp/index.html.twig', [
            'murder_parties' => $murderParties,
            'filters' => compact('keyword','minPlayers','maxPlayers','minDuration','maxDuration'),
        ]);
    }

    #[Route('/new', name: 'admin_mp_new', methods:['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $mp = new MurderParty();
        $form = $this->createForm(MurderPartyType::class, $mp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $coverFile */
            $coverFile = $form->get('coverImageUrl')->getData();

            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    $originalFilename
                );
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();

                try {
                    $coverFile->move($this->getParameter('covers_directory'), $newFilename);
                    $mp->setCoverImageUrl($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossible de sauvegarder l\'image.');
                }
            }

            $em->persist($mp);
            $em->flush();

            $this->addFlash('success', 'Murder Party créée !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/mp/new.html.twig', [
            'form' => $form->createView(),
            'mp'   => $mp,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_mp_edit', methods: ['GET','POST'])]
    public function edit(MurderParty $mp, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MurderPartyType::class, $mp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $coverFile */
            $coverFile = $form->get('coverImageUrl')->getData();

            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    $originalFilename
                );
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();

                try {
                    $coverFile->move($this->getParameter('covers_directory'), $newFilename);
                    $mp->setCoverImageUrl($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossible de sauvegarder l\'image.');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Murder Party mise à jour.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/mp/edit.html.twig', [
            'form' => $form->createView(),
            'mp'   => $mp,
        ]);
    }

    #[Route('/{id}/show', name: 'admin_mp_show', methods: ['GET'])]
    public function show(MurderParty $mp): Response
    {
        return $this->render('admin/mp/show.html.twig', [
            'mp' => $mp,
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
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/character/{id}/delete', name: 'admin_character_delete', methods: ['POST'])]
    public function deleteCharacter(Character $character, Request $request, EntityManagerInterface $em): Response
    {
       $mpId = $character->getMurderParty()->getId();
        if ($this->isCsrfTokenValid('delete_char_' . $character->getId(), $request->request->get('_token'))) {

            // Déconnecter tous les GamePlayers liés
            foreach ($character->getGamePlayers() as $gp) {
                $gp->setCharacter(null);
            }

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