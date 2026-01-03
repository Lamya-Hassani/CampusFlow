<?php

namespace App\Controller\Admin;

use App\Repository\GradeRepository;
use App\Repository\StudentRepository;
use App\Repository\ClasseRepository;
use App\Repository\TeacherRepository;
use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        StudentRepository $studentRepository,
        ClasseRepository $classeRepository,
        TeacherRepository $teacherRepository,
        ScheduleRepository $scheduleRepository,
        GradeRepository $gradeRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = [
            'students' => $studentRepository->count([]),
            'classes' => $classeRepository->count([]),
            'teachers' => $teacherRepository->count([]),
            'schedules' => $scheduleRepository->count([]),
            'grades' => $gradeRepository->count([]),
        ];

        $studentsByClass = $classeRepository->getStudentsCountByClass();
        $recentStudents = $studentRepository->findBy([], ['enrollmentDate' => 'DESC'], 5);
        $success = $gradeRepository->getSuccessRate();
        $monthlyAverages = $gradeRepository->getMonthlyAverages();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'studentsByClass' => $studentsByClass,
            'recentStudents' => $recentStudents,
            'success' => $success,
            'monthlyAverages' => $monthlyAverages,
        ]);
    }
}

