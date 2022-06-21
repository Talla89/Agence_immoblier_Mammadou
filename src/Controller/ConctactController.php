<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConctactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        //On stocque les rendez-vous dans la base de données
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);


        if ($form->isValid() && $form->isSubmitted()) {
            $entityManager->persist($contact);
            $entityManager->flush();
            $this->addFlash('réussit', 'votre rendez est prise en compte');
        }


        return $this->render('conctact/index.html.twig', [
            'controller_name' => 'ConctactController',
            'contact_adress' => $this->getParameter('app.contact.adress'),
            'contact_phone' => $this->getParameter('app.phone.adress'),
            'contact_email' => $this->getParameter('app.email.adress'),
            'form' => $form->createView()
        ]);
    }
}
