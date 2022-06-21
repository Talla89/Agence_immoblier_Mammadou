<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annonces')]
class AnnoncesController extends AbstractController
{
    #[Route('/', name: 'app_annonces_index', methods: ['GET'])]
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_annonces_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AnnoncesRepository $annoncesRepository): Response
    {
        $annonce = new Annonces();
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annoncesRepository->add($annonce, true);
            //On récupère les images transmises
            $images = $form->get('images')->getData();

            //On fait une boucle pour des images multiples
            foreach ($images as $image) {
                //On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                //On stocke l'image dans la base de données

                $img = new Images();
                $img->setName($fichier);
                $annonce->addImage($img);
            }



            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annonces_show', methods: ['GET'])]
    public function show(Annonces $annonce): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonce);
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_annonces_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annoncesRepository->add($annonce, true);

            //On récupère les images transmises
            $images = $form->get('images')->getData();

            //On fait une boucle pour des images multiples
            foreach ($images as $image) {
                //On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                //On stocke l'image dans la base de données

                $img = new Images();
                $img->setName($fichier);
                $annonce->addImage($img);
            }

            $entityManager = $this->Doctrine->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            // informe Doctrine que l’on veut ajouter
            $entityManager->persist($annonce);
            // Executer la requête et d’envoyer tout ce qui à été persisté avant a la BD
            $entityManager->flush();
            $this->addFlash('réussit', 'votre ajout est prise en compte');



            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annonces_delete', methods: ['POST'])]
    public function delete(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            $annoncesRepository->remove($annonce, true);
        }

        return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
    }

    //On définit une fonction pour supprimer les images

    public function deleteImage(Images $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $nom = $image->getName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);


            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
    #[Route('/annonces', name: 'annonces')]
    public function sow(ManagerRegistry $doctrine): Response

    {
        $annonces = $doctrine->getRepository(Annonces::class)->findAll();

        if (!$annonces) {
            throw $this->createNotFoundException(
                'No annonces found for id '
            );
        }

        return $this->render('annonces/index.html.twig', [
            'controller_name' => 'AnnoncesController',
            'annonces' => $annonces
        ]);
    }
}
