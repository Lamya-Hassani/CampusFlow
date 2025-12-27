<?php

namespace App\Controller\Admin;

use App\Entity\Schedule;
use App\Form\ScheduleType;
use App\Repository\ScheduleRepository;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/schedule', name: 'admin_schedule_')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ScheduleRepository $scheduleRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $classId = $request->query->get('class');
        $schedules = $classId 
            ? $scheduleRepository->findBy(['classe' => $classId])
            : $scheduleRepository->findAll();

        return $this->render('admin/schedule/index.html.twig', [
            'schedules' => $schedules,
            'selectedClass' => $classId,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScheduleRepository $scheduleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $schedule = new Schedule();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check for conflicts
            $conflicts = $scheduleRepository->findConflicts(
                $schedule->getDayOfWeek(),
                $schedule->getStartTime(),
                $schedule->getEndTime(),
                $schedule->getTeacher(),
                $schedule->getClasse(),
                $schedule->getRoom()
            );

            if (!empty($conflicts)) {
                $this->addFlash('error', 'Conflit détecté ! Un créneau existe déjà pour cet enseignant, cette classe ou cette salle à cette heure.');
            } else {
                // Validate duration
                $duration = $schedule->getDuration();
                if ($duration < 60 || $duration > 240) {
                    $this->addFlash('error', 'La durée du cours doit être entre 1h et 4h.');
                } else {
                    $this->em->persist($schedule);
                    $this->em->flush();

                    $this->addFlash('success', 'Créneau créé avec succès.');

                    return $this->redirectToRoute('admin_schedule_index');
                }
            }
        }

        return $this->render('admin/schedule/new.html.twig', [
            'schedule' => $schedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Schedule $schedule, ScheduleRepository $scheduleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check for conflicts (excluding current schedule)
            $conflicts = $scheduleRepository->findConflicts(
                $schedule->getDayOfWeek(),
                $schedule->getStartTime(),
                $schedule->getEndTime(),
                $schedule->getTeacher(),
                $schedule->getClasse(),
                $schedule->getRoom(),
                $schedule->getId()
            );

            if (!empty($conflicts)) {
                $this->addFlash('error', 'Conflit détecté ! Un créneau existe déjà pour cet enseignant, cette classe ou cette salle à cette heure.');
            } else {
                // Validate duration
                $duration = $schedule->getDuration();
                if ($duration < 60 || $duration > 240) {
                    $this->addFlash('error', 'La durée du cours doit être entre 1h et 4h.');
                } else {
                    $this->em->flush();

                    $this->addFlash('success', 'Créneau modifié avec succès.');

                    return $this->redirectToRoute('admin_schedule_index');
                }
            }
        }

        return $this->render('admin/schedule/edit.html.twig', [
            'schedule' => $schedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Schedule $schedule): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$schedule->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($schedule);
            $this->em->flush();

            $this->addFlash('success', 'Créneau supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_schedule_index');
    }

    #[Route('/class/{classId}', name: 'by_class', methods: ['GET'])]
    public function byClass(int $classId, ClasseRepository $classeRepository, ScheduleRepository $scheduleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $classe = $classeRepository->find($classId);
        if (!$classe) {
            throw $this->createNotFoundException('Classe non trouvée');
        }

        $schedules = $scheduleRepository->findBy(['classe' => $classe], ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']);

        return $this->render('admin/schedule/by_class.html.twig', [
            'classe' => $classe,
            'schedules' => $schedules,
        ]);
    }
}

