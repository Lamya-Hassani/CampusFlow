<?php

namespace App\Controller\Teacher;

use App\Entity\Grade;
use App\Entity\Subject;
use App\Repository\ClasseRepository;
use App\Repository\GradeRepository;
use App\Repository\TeacherRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teacher/grades', name: 'teacher_grades_')]
class GradeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        TeacherRepository $teacherRepository,
        GradeRepository $gradeRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);
        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $grades = $gradeRepository->findBy(
            ['teacher' => $teacher],
            ['createdAt' => 'DESC']
        );

        return $this->render('teacher/grades/index.html.twig', [
            'teacher' => $teacher,
            'grades' => $grades,
        ]);
    }

    #[Route('/students', name: 'students')]
    public function students(
        TeacherRepository $teacherRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);
        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $query = trim((string) $request->query->get('q', ''));

        // classes where this teacher has at least one schedule
        $classes = [];
        foreach ($teacher->getSchedules() as $schedule) {
            $classe = $schedule->getClasse();
            $classes[$classe->getId()] = $classe;
        }

        // build students list with subjects he teaches them
        $studentsInfo = [];
        foreach ($classes as $classe) {
            foreach ($classe->getStudents() as $student) {
                $studentId = $student->getId();
                if (!isset($studentsInfo[$studentId])) {
                    $studentsInfo[$studentId] = [
                        'student' => $student,
                        'classe' => $classe,
                        'subjects' => [],
                    ];
                }

                foreach ($classe->getSchedules() as $schedule) {
                    if ($schedule->getTeacher() === $teacher) {
                        $subject = $schedule->getSubject();
                        $studentsInfo[$studentId]['subjects'][$subject->getId()] = $subject;
                    }
                }
            }
        }

        // apply in-memory filter for search
        if ($query !== '') {
            $lower = mb_strtolower($query);
            $studentsInfo = array_filter($studentsInfo, function (array $info) use ($lower): bool {
                $studentName = mb_strtolower($info['student']->getFullName());
                $className = mb_strtolower($info['classe']->getName());

                $subjectMatch = false;
                foreach ($info['subjects'] as $subject) {
                    if (str_contains(mb_strtolower($subject->getName()), $lower)) {
                        $subjectMatch = true;
                        break;
                    }
                }

                return str_contains($studentName, $lower)
                    || str_contains($className, $lower)
                    || $subjectMatch;
            });
        }

        return $this->render('teacher/grades/students.html.twig', [
            'teacher' => $teacher,
            'studentsInfo' => $studentsInfo,
            'query' => $query,
        ]);
    }

    #[Route('/student/{id}', name: 'student')]
    public function studentGrades(
        int $id,
        TeacherRepository $teacherRepository,
        StudentRepository $studentRepository,
        GradeRepository $gradeRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);
        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $student = $studentRepository->find($id);
        if (!$student) {
            throw $this->createNotFoundException('Étudiant introuvable');
        }

        $classe = $student->getClasse();
        $isAllowed = false;
        foreach ($classe->getSchedules() as $schedule) {
            if ($schedule->getTeacher() === $teacher) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir les notes de cet étudiant.');
        }

        $grades = $gradeRepository->findBy(
            ['student' => $student, 'teacher' => $teacher],
            ['createdAt' => 'DESC']
        );

        return $this->render('teacher/grades/student.html.twig', [
            'teacher' => $teacher,
            'student' => $student,
            'grades' => $grades,
        ]);
    }

    #[Route('/student/{id}/add', name: 'add_for_student')]
    public function addForStudent(
        int $id,
        TeacherRepository $teacherRepository,
        StudentRepository $studentRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);
        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $student = $studentRepository->find($id);
        if (!$student) {
            throw $this->createNotFoundException('Étudiant introuvable');
        }

        $classe = $student->getClasse();

        // subjects that this teacher teaches to this class
        $subjects = [];
        foreach ($classe->getSchedules() as $schedule) {
            if ($schedule->getTeacher() === $teacher) {
                $subj = $schedule->getSubject();
                $subjects[$subj->getId()] = $subj;
            }
        }
        if (empty($subjects)) {
            throw $this->createAccessDeniedException('Vous n\'enseignez aucune matière à cet étudiant.');
        }

        if ($request->isMethod('POST')) {
            $subjectId = (int) $request->request->get('subject');
            $type = $request->request->get('type') ?: 'control';
            $semester = (int) $request->request->get('semester') ?: 1;
            $value = $request->request->get('value');

            if ($subjectId && $value !== null && $value !== '') {
                $subject = $subjects[$subjectId] ?? null;
                if (!$subject) {
                    throw $this->createAccessDeniedException('Matière invalide.');
                }

                $grade = new Grade();
                $grade
                    ->setStudent($student)
                    ->setSubject($subject)
                    ->setTeacher($teacher)
                    ->setClasse($classe)
                    ->setType($type)
                    ->setSemester($semester)
                    ->setValue(number_format((float) $value, 2, '.', ''));

                $em->persist($grade);
                $em->flush();

                $this->addFlash('success', 'Note ajoutée.');
                return $this->redirectToRoute('teacher_grades_student', ['id' => $student->getId()]);
            }

            $this->addFlash('error', 'Veuillez remplir tous les champs.');
        }

        return $this->render('teacher/grades/add_for_student.html.twig', [
            'teacher' => $teacher,
            'student' => $student,
            'classe' => $classe,
            'subjects' => $subjects,
        ]);
    }
}