<?php

namespace App\Controller\Admin;

use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/grades', name: 'admin_grades_')]
class GradeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(GradeRepository $gradeRepository): Response
    {
        // Optional: restrict access if needed
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/grade/index.html.twig', [
            'grades' => $gradeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $grade = new Grade();
        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($grade);
            $this->em->flush();

            $this->addFlash('success', 'Grade créé avec succès.');

            return $this->redirectToRoute('admin_grades_index');
        }

        return $this->render('admin/grade/new.html.twig', [
            'grade' => $grade,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Grade $grade): Response
    {
        return $this->render('admin/grade/show.html.twig', [
            'grade' => $grade,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grade $grade): Response
    {
        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Grade modifié avec succès.');

            return $this->redirectToRoute('admin_grades_index');
        }

        return $this->render('admin/grade/edit.html.twig', [
            'grade' => $grade,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Grade $grade): Response
    {
        if ($this->isCsrfTokenValid('delete'.$grade->getId(), $request->request->get('_token'))) {
            $this->em->remove($grade);
            $this->em->flush();

            $this->addFlash('success', 'Grade supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_grades_index');
    }
}
