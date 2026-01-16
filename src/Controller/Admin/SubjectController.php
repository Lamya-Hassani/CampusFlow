<?php

namespace App\Controller\Admin;

use App\Entity\Subject;
use App\Form\SubjectType;
use App\Repository\SubjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/subject', name: 'admin_subject_')]
class SubjectController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(SubjectRepository $subjectRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search');
        $subjects = $search
            ? $subjectRepository->findBySearch($search)
            : $subjectRepository->findAll();

        return $this->render('admin/subject/index.html.twig', [
            'subjects' => $subjects,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($subject);
            $this->em->flush();

            $this->addFlash('success', 'Matière créée avec succès.');

            return $this->redirectToRoute('admin_subject_index');
        }

        return $this->render('admin/subject/new.html.twig', [
            'subject' => $subject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subject $subject): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Matière modifiée avec succès.');

            return $this->redirectToRoute('admin_subject_index');
        }

        return $this->render('admin/subject/edit.html.twig', [
            'subject' => $subject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Subject $subject): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$subject->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($subject);
            $this->em->flush();

            $this->addFlash('success', 'Matière supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_subject_index');
    }
}

