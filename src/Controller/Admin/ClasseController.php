<?php

namespace App\Controller\Admin;

use App\Entity\Classe;
use App\Form\ClasseType;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/classe', name: 'admin_classe_')]
class ClasseController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ClasseRepository $classeRepository, Request $request): Response
    {
        // denyAccessUnlessGranted is a built-in security feature that 
        // ensures only users with the appropriate role can access a specific controller action
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // search is a query parameter that allows users to search for classes by name or field
        $search = $request->query->get('search');
        $classes = $search
            // findByNameOrField is a custom repository method that searches for classes by name or field
            // if search is not empty, find classes by name or field
            ? $classeRepository->findByNameOrField($search)
            // if search is empty, find all classes
            : $classeRepository->findAll();

        return $this->render('admin/classe/index.html.twig', [
            'classes' => $classes,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $classe = new Classe();
        $form = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // presist is a built-in method that adds the object to the entity manager
            $this->em->persist($classe); 
            // flush is a built-in method that saves the object to the database
            $this->em->flush();

            $this->addFlash('success', 'Classe créée avec succès.');

            return $this->redirectToRoute('admin_classe_index');
        }

        return $this->render('admin/classe/new.html.twig', [
            'classe' => $classe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Classe $classe): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/classe/show.html.twig', [
            'classe' => $classe,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    //Classe $classe : entité existante chargée à partir de l’id.
    public function edit(Request $request, Classe $classe): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Crée un formulaire pré-rempli avec les données de $classe
        $form = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Classe modifiée avec succès.');

            return $this->redirectToRoute('admin_classe_index');
        }

        return $this->render('admin/classe/edit.html.twig', [
            'classe' => $classe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Classe $classe): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Le token attendu porte l’ID delete{id}.
        // getPayload()->getString('_token') récupère la valeur envoyée dans le corps de la requête.
        if ($this->isCsrfTokenValid('delete'.$classe->getId(), $request->getPayload()->getString('_token'))) {
            if ($classe->getStudentCount() > 0) {
                $this->addFlash('error', 'Impossible de supprimer une classe contenant des étudiants.');
            } else {
                $this->em->remove($classe);
                $this->em->flush();

                $this->addFlash('success', 'Classe supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('admin_classe_index');
    }
}

