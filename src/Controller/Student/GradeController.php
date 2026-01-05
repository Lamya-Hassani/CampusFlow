<?php

namespace App\Controller\Student;

use App\Repository\StudentRepository;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/grades', name: 'student_grades_')]
#[IsGranted('ROLE_STUDENT')]
class GradeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        StudentRepository $studentRepo,
        GradeRepository $gradeRepo
    ): Response {
        $user = $this->getUser();
        $student = $studentRepo->findOneBy(['user' => $user]);

        if (!$student) {
            throw $this->createNotFoundException('Profil étudiant non trouvé.');
        }

        $grades = $gradeRepo->findBy(
            ['student' => $student],
            ['createdAt' => 'DESC']
        );

        return $this->render('student/grades/index.html.twig', [
            'student' => $student,
            'grades' => $grades,
        ]);
    }
}
