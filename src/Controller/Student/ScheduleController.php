<?php

namespace App\Controller\Student;

use App\Repository\StudentRepository;
use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/schedule', name: 'student_schedule_')]
#[IsGranted('ROLE_STUDENT')]
class ScheduleController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        StudentRepository $studentRepo,
        ScheduleRepository $scheduleRepo
    ): Response {
        $user = $this->getUser();
        $student = $studentRepo->findOneBy(['user' => $user]);

        if (!$student) {
            throw $this->createNotFoundException('Profil étudiant non trouvé.');
        }

        $schedules = $scheduleRepo->findBy(
            ['classe' => $student->getClasse()],
            ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']
        );

        $schedulesByDay = [];
        foreach ($schedules as $schedule) {
            $schedulesByDay[$schedule->getDayOfWeek()][] = $schedule;
        }

        return $this->render('student/schedule/index.html.twig', [
            'student' => $student,
            'schedulesByDay' => $schedulesByDay,
        ]);
    }
}
