<?php

namespace App\Controller\Admin;

use App\Entity\Student;
use App\Entity\User;
use App\Form\StudentType;
use App\Form\GradeType;
use App\Repository\StudentRepository;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_STUDENT']);
            $user->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($user);
            $student->setUser($user);
            $student->setEnrollmentDate(new \DateTimeImmutable());

            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir') . '/public/uploads/profiles', $newFilename);
                $student->setProfilePicture('uploads/profiles/' . $newFilename);
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
            $newEmail = $form->get('email')->getData();
            if ($newEmail !== $student->getUser()->getEmail()) {
                $student->getUser()->setEmail($newEmail);
            }

            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $student->getUser()->setPassword($this->passwordHasher->hashPassword($student->getUser(), $newPassword));
            }

            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                if ($student->getProfilePicture()) {
                    $oldFile = $this->parameterBag->get('kernel.project_dir') . '/public/' . $student->getProfilePicture();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();
                $profilePicture->move($this->parameterBag->get('kernel.project_dir') . '/public/uploads/profiles', $newFilename);
                $student->setProfilePicture('uploads/profiles/' . $newFilename);
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

        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->getPayload()->getString('_token'))) {
            if ($student->getProfilePicture()) {
                $file = $this->parameterBag->get('kernel.project_dir') . '/public/' . $student->getProfilePicture();
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

    // ========= BULLETIN DE NOTES =========

    #[Route('/{id}/bulletin/{semester}', name: 'bulletin', methods: ['GET'], requirements: ['semester' => '\d+'], defaults: ['semester' => 1])]
    public function bulletin(Student $student, int $semester, GradeRepository $gradeRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $grades = $gradeRepo->findBy(['student' => $student, 'semester' => $semester]);

        $totalCoef = 0;
        $totalPoints = 0;
        foreach ($grades as $grade) {
            $coef = $grade->getSubject()->getCoefficient() ?? 1;
            $totalCoef += $coef;
            $totalPoints += $grade->getValue() * $coef;
        }
        $average = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0;

        return $this->render('admin/student/bulletin_preview.html.twig', [
            'student' => $student,
            'grades' => $grades,
            'semester' => $semester,
            'average' => $average,
            'totalCoef' => $totalCoef,
        ]);
    }

    #[Route('/{id}/bulletin/{semester}/edit', name: 'edit_bulletin', methods: ['GET', 'POST'])]
    public function editBulletin(Request $request, Student $student, int $semester, GradeRepository $gradeRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $grades = $gradeRepo->findBy(['student' => $student, 'semester' => $semester]);

        $form = $this->createFormBuilder()
            ->add('grades', CollectionType::class, [
                'entry_type' => GradeType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'data' => $grades,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Notes mises à jour avec succès.');

            return $this->redirectToRoute('admin_student_bulletin', [
                'id' => $student->getId(),
                'semester' => $semester
            ]);
        }

        return $this->render('admin/student/edit_bulletin.html.twig', [
            'student' => $student,
            'form' => $form->createView(),
            'semester' => $semester,
        ]);
    }

    #[Route('/{id}/bulletin/{semester}/pdf', name: 'bulletin_pdf', methods: ['GET'])]
    public function bulletinPdf(Student $student, int $semester, GradeRepository $gradeRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $grades = $gradeRepo->findBy(['student' => $student, 'semester' => $semester]);

        $totalCoef = 0;
        $totalPoints = 0;
        foreach ($grades as $grade) {
            $coef = $grade->getSubject()->getCoefficient() ?? 1;
            $totalCoef += $coef;
            $totalPoints += $grade->getValue() * $coef;
        }
        $average = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0;

        $html = $this->renderView('admin/student/bulletin_pdf_template.html.twig', [
            'student' => $student,
            'grades' => $grades,
            'semester' => $semester,
            'average' => $average,
            'totalCoef' => $totalCoef,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVuSans'); // Support accents
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'bulletin_' . preg_replace('/[^A-Za-z0-9]/', '_', $student->getFullName()) . '_S' . $semester . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}