<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactType;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $em
    ) {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $extra = '';
            if (!empty($data['sousMenu']))        $extra .= "\nType d'événement : " . $data['sousMenu'];
            if (!empty($data['nombrePersonnes'])) $extra .= "\nNombre de participants : " . $data['nombrePersonnes'];
            if (!empty($data['dateEvenement']))   $extra .= "\nDate souhaitée : " . $data['dateEvenement'];

            $contactMessage = new ContactMessage();
            $contactMessage
                ->setNom($data['nom'])
                ->setPrenom($data['prenom'])
                ->setEmail($data['email'])
                ->setSubject($data['sujet'])
                ->setMessage($data['message'] . $extra);

            $em->persist($contactMessage);
            $em->flush();

            $email = (new Email())
                ->from('meurtrehuisclos@gmail.com')
                ->replyTo($data['email'])
                ->to('meurtrehuisclos@gmail.com')
                ->subject('Contact — ' . $data['sujet'])
                ->html($this->renderView('emails/contact_email.html.twig', ['data' => $data]));

            $mailer->send($email);

            $this->addFlash('success', 'Message envoyé !');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}