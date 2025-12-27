<?php

namespace App\Controller\Teacher;

use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teacher', name: 'teacher_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ScheduleRepository $scheduleRepository, \App\Repository\TeacherRepository $teacherRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);

        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $schedules = $scheduleRepository->findBy(
            ['teacher' => $teacher],
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

        return $this->render('teacher/dashboard.html.twig', [
            'teacher' => $teacher,
            'schedulesByDay' => $schedulesByDay,
        ]);
    }
}

