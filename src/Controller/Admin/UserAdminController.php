<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserAdminController extends AbstractController
{
    #[Route('', name: 'admin_user_index')]
    public function index(UserRepository $repo): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $repo->findBy(['isDeleted' => false], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show')]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/toggle-role', name: 'admin_user_toggle_role', methods: ['POST'])]
    public function toggleRole(User $user, EntityManagerInterface $em): Response
    {
        $user->setRole($user->getRole() === 'admin' ? 'user' : 'admin');
        $em->flush();
        $this->addFlash('success', 'Rôle mis à jour.');
        return $this->redirectToRoute('admin_user_index');
    }

    // #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    // public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    // {
    //    if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
    //        // Suppression douce
    //        $user->setIsDeleted(true);
    //        $user->setEmail('deleted_' . $user->getId() . '@deleted.com');
    //        $em->flush();
   //         $this->addFlash('success', 'Utilisateur supprimé.');
    //    }
    //    return $this->redirectToRoute('admin_user_index');
    //}
}