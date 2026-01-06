<?php

namespace App\Controller\Admin;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\TeacherRepository;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/admin/teacher', name: 'admin_teacher_')]
class TeacherController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ParameterBagInterface $parameterBag
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(TeacherRepository $teacherRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search');
        $teachers = $search
            ? $teacherRepository->findByNameOrEmail($search)
            : $teacherRepository->findAll();

        return $this->render('admin/teacher/index.html.twig', [
            'teachers' => $teachers,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $teacher = new Teacher();
        $form = $this->createForm(TeacherType::class, $teacher, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Create User
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_TEACHER']);
            $user->setCreatedAt(new \DateTime());

            $this->em->persist($user);
            $teacher->setUser($user);

            // Handle file upload
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                $newFilename = uniqid().'.'.$profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir').'/public/uploads/profiles', $newFilename);
                $teacher->setProfilePicture('uploads/profiles/'.$newFilename);
            }

            $this->em->persist($teacher);
            $this->em->flush();

            $this->addFlash('success', 'Enseignant créé avec succès.');

            return $this->redirectToRoute('admin_teacher_index');
        }

        return $this->render('admin/teacher/new.html.twig', [
            'teacher' => $teacher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Teacher $teacher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/teacher/show.html.twig', [
            'teacher' => $teacher,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Teacher $teacher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(TeacherType::class, $teacher, [
            'is_new' => false,
            'email' => $teacher->getUser()->getEmail(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update email if changed
            $newEmail = $form->get('email')->getData();
            if ($newEmail !== $teacher->getUser()->getEmail()) {
                $teacher->getUser()->setEmail($newEmail);
            }

            // Update password if provided
            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $teacher->getUser()->setPassword($this->passwordHasher->hashPassword($teacher->getUser(), $newPassword));
            }

            // Handle file upload
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                // Delete old picture if exists
                if ($teacher->getProfilePicture()) {
                    $oldFile = $this->parameterBag->get('kernel.project_dir').'/public/'.$teacher->getProfilePicture();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $newFilename = uniqid().'.'.$profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir').'/public/uploads/profiles', $newFilename);
                $teacher->setProfilePicture('uploads/profiles/'.$newFilename);
            }

            $this->em->flush();

            $this->addFlash('success', 'Enseignant modifié avec succès.');

            return $this->redirectToRoute('admin_teacher_index');
        }

        return $this->render('admin/teacher/edit.html.twig', [
            'teacher' => $teacher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/planning', name: 'planning', methods: ['GET'])]
    public function planning(Teacher $teacher, ScheduleRepository $scheduleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $schedules = $scheduleRepository->findBy(['teacher' => $teacher], ['startTime' => 'ASC']);

        // Organize schedules by day
        $weekCalendar = [
            'Monday' => [],
            'Tuesday' => [],
            'Wednesday' => [],
            'Thursday' => [],
            'Friday' => [],
            'Saturday' => [],
        ];

        foreach ($schedules as $schedule) {
            $weekCalendar[$schedule->getDayOfWeek()][] = $schedule;
        }

        return $this->render('admin/teacher/planning.html.twig', [
            'teacher' => $teacher,
            'weekCalendar' => $weekCalendar,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Teacher $teacher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$teacher->getId(), $request->getPayload()->getString('_token'))) {
            // Delete profile picture if exists
            if ($teacher->getProfilePicture()) {
                $file = $this->parameterBag->get('kernel.project_dir').'/public/'.$teacher->getProfilePicture();
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $user = $teacher->getUser();
            $this->em->remove($teacher);
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', 'Enseignant supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_teacher_index');
    }
}

