<?php

namespace App\Controller\Student;

use App\Repository\StudentRepository;
use App\Repository\ScheduleRepository;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student', name: 'student_')]
#[IsGranted('ROLE_STUDENT')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        StudentRepository $studentRepo, 
        ScheduleRepository $scheduleRepo,
        GradeRepository $gradeRepo
    ): Response
    {
        // Get the logged-in student
        $user = $this->getUser();
        $student = $studentRepo->findOneBy(['user' => $user]);

        if (!$student) {
            throw $this->createNotFoundException('Profil étudiant non trouvé.');
        }

        // Get today's schedule
        // Assuming dayOfWeek is stored as 'Monday', 'Tuesday' or '1', '2'. 
        // Let's assume PHP date('l') format: 'Monday'.
        $today = date('l'); 
        $todaysClasses = $scheduleRepo->findBy([
            'classe' => $student->getClasse(),
            'dayOfWeek' => $today
        ], ['startTime' => 'ASC']);

        // Get recent grades (limit 5)
        $recentGrades = $gradeRepo->findBy(
            ['student' => $student], 
            ['createdAt' => 'DESC'], 
            5
        );

        return $this->render('student/dashboard.html.twig', [
            'student' => $student,
            'todaysClasses' => $todaysClasses,
            'recentGrades' => $recentGrades,
        ]);
    }
}