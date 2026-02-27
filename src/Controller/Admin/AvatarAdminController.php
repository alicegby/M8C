<?php

namespace App\Controller\Admin;

use App\Entity\Avatar;
use App\Form\AvatarType;
use App\Repository\AvatarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/avatars')]
class AvatarAdminController extends AbstractController
{
    private EntityManagerInterface $em;
    private AvatarRepository $avatarRepository;

    public function __construct(EntityManagerInterface $em, AvatarRepository $avatarRepository)
    {
        $this->em = $em;
        $this->avatarRepository = $avatarRepository;
    }

    #[Route('/', name: 'admin_avatars_index', methods: ['GET'])]
    public function index(): Response
    {
        $avatars = $this->avatarRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/avatar/index.html.twig', [
            'avatars' => $avatars,
        ]);
    }

    #[Route('/new', name: 'admin_avatars_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $avatar = new Avatar();
        $form = $this->createForm(AvatarType::class, $avatar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload du fichier
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();
                $avatarFile->move($this->getParameter('avatar_mp_directory'), $newFilename);
                $avatar->setImageUrl('/uploads/avatar_mp/'.$newFilename);
            }

            $this->em->persist($avatar);
            $this->em->flush();

            $this->addFlash('success', 'Avatar créé avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/avatar/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/show', name: 'admin_avatars_show', methods: ['GET'])]
    public function show(Avatar $avatar): Response
    {
        return $this->render('admin/avatar/show.html.twig', [
            'avatar' => $avatar,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_avatars_delete', methods: ['POST'])]
    public function delete(Request $request, Avatar $avatar): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avatar->getId(), $request->request->get('_token'))) {
            
            // Supprimer le fichier si existant
            if ($avatar->getImageUrl()) {
                $filePath = $this->getParameter('kernel.project_dir').'/public'.$avatar->getImageUrl();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $this->em->remove($avatar);
            $this->em->flush();
            $this->addFlash('success', 'Avatar supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}