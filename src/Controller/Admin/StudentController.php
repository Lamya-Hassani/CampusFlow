<?php

namespace App\Controller\Admin;

use App\Entity\Student;
use App\Entity\User;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/admin/student', name: 'admin_student_')]
class StudentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ParameterBagInterface $parameterBag
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search', '');
        $students = $search 
            ? $studentRepository->search($search)
            : $studentRepository->findAll();

        return $this->render('admin/student/index.html.twig', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $student = new Student();
        $form = $this->createForm(StudentType::class, $student, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Create User
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_STUDENT']);
            $user->setCreatedAt(new \DateTime());

            $this->em->persist($user);
            $student->setUser($user);
            $student->setEnrollmentDate(new \DateTime());

            // Handle file upload
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                $newFilename = uniqid().'.'.$profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir').'/public/uploads/profiles', $newFilename);
                $student->setProfilePicture('uploads/profiles/'.$newFilename);
            }

            $this->em->persist($student);
            $this->em->flush();

            $this->addFlash('success', 'Étudiant créé avec succès.');

            return $this->redirectToRoute('admin_student_index');
        }

        return $this->render('admin/student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(StudentType::class, $student, [
            'is_new' => false,
            'email' => $student->getUser()->getEmail(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update email if changed
            $newEmail = $form->get('email')->getData();
            if ($newEmail !== $student->getUser()->getEmail()) {
                $student->getUser()->setEmail($newEmail);
            }

            // Update password if provided
            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $student->getUser()->setPassword($this->passwordHasher->hashPassword($student->getUser(), $newPassword));
            }

            // Handle file upload
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                // Delete old picture if exists
                if ($student->getProfilePicture()) {
                    $oldFile = $this->parameterBag->get('kernel.project_dir').'/public/'.$student->getProfilePicture();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $newFilename = uniqid().'.'.$profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir').'/public/uploads/profiles', $newFilename);
                $student->setProfilePicture('uploads/profiles/'.$newFilename);
            }

            $this->em->flush();

            $this->addFlash('success', 'Étudiant modifié avec succès.');

            return $this->redirectToRoute('admin_student_index');
        }

        return $this->render('admin/student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Student $student): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->getPayload()->getString('_token'))) {
            // Delete profile picture if exists
            if ($student->getProfilePicture()) {
                $file = $this->parameterBag->get('kernel.project_dir').'/public/'.$student->getProfilePicture();
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $user = $student->getUser();
            $this->em->remove($student);
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', 'Étudiant supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_student_index');
    }
}

