<?php

namespace App\Controller\Student;

use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student', name: 'student_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ScheduleRepository $scheduleRepository, \App\Repository\StudentRepository $studentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT');

        $user = $this->getUser();
        $student = $studentRepository->findOneBy(['user' => $user]);

        if (!$student || !$student->getClasse()) {
            throw $this->createNotFoundException('Profil étudiant ou classe non trouvé');
        }

        $schedules = $scheduleRepository->findBy(
            ['classe' => $student->getClasse()],
            ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']
        );

        // Group schedules by day
        $schedulesByDay = [];
        foreach ($schedules as $schedule) {
            $day = $schedule->getDayOfWeek();
            if (!isset($schedulesByDay[$day])) {
                $schedulesByDay[$day] = [];
            }
            $schedulesByDay[$day][] = $schedule;
        }

        return $this->render('student/dashboard.html.twig', [
            'student' => $student,
            'schedulesByDay' => $schedulesByDay,
        ]);
    }
}

