<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/contact-messages')]
#[IsGranted('ROLE_ADMIN')]
class ContactMessageAdminController extends AbstractController
{
    #[Route('', name: 'admin_contact_index')]
    public function index(ContactMessageRepository $repo): Response
    {
        return $this->render('admin/contact_message/index.html.twig', [
            'unread' => $repo->findBy(['isRead' => false], ['sentAt' => 'DESC']),
            'read' => $repo->findBy(['isRead' => true], ['sentAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'admin_contact_show')]
    public function show(ContactMessage $message, EntityManagerInterface $em): Response
    {
        if (!$message->isRead()) {
            $message->setIsRead(true);
            $em->flush();
        }

        return $this->render('admin/contact_message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_contact_delete', methods: ['POST'])]
    public function delete(ContactMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_contact_' . $message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }
        return $this->redirectToRoute('admin_contact_index');
    }
}