<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\Classe;
use App\Entity\Subject;
use App\Entity\Schedule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create Admin User
        $adminUser = new User();
        $adminUser->setEmail('admin@campusflow.com');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setCreatedAt(new \DateTime());
        $manager->persist($adminUser);

        // Create Subjects
        $subjects = [];
        $subjectNames = [
            ['name' => 'Mathématiques', 'code' => 'MATH101', 'coef' => '3.00'],
            ['name' => 'Informatique', 'code' => 'INFO101', 'coef' => '4.00'],
            ['name' => 'Base de données', 'code' => 'BD101', 'coef' => '3.50'],
            ['name' => 'Réseaux', 'code' => 'RES101', 'coef' => '3.00'],
            ['name' => 'Programmation', 'code' => 'PROG101', 'coef' => '4.50'],
        ];

        foreach ($subjectNames as $subj) {
            $subject = new Subject();
            $subject->setName($subj['name']);
            $subject->setCode($subj['code']);
            $subject->setCoefficient($subj['coef']);
            $subject->setDescription('Description de ' . $subj['name']);
            $manager->persist($subject);
            $subjects[] = $subject;
        }

        // Create Teachers
        $teachers = [];
        $teacherData = [
            ['first' => 'Ahmed', 'last' => 'Benali', 'email' => 'ahmed.benali@campusflow.com', 'specialty' => 'Informatique', 'grade' => 'Professeur'],
            ['first' => 'Fatima', 'last' => 'Alaoui', 'email' => 'fatima.alaoui@campusflow.com', 'specialty' => 'Mathématiques', 'grade' => 'Maître de conférences'],
            ['first' => 'Mohamed', 'last' => 'Tazi', 'email' => 'mohamed.tazi@campusflow.com', 'specialty' => 'Réseaux', 'grade' => 'Professeur'],
        ];

        foreach ($teacherData as $tData) {
            $user = new User();
            $user->setEmail($tData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'teacher123'));
            $user->setRoles(['ROLE_TEACHER']);
            $user->setCreatedAt(new \DateTime());
            $manager->persist($user);

            $teacher = new Teacher();
            $teacher->setUser($user);
            $teacher->setFirstName($tData['first']);
            $teacher->setLastName($tData['last']);
            $teacher->setSpecialty($tData['specialty']);
            $teacher->setGrade($tData['grade']);
            $teacher->setPhone('0612345678');
            
            // Assign subjects
            if ($tData['specialty'] === 'Informatique') {
                $teacher->addSubject($subjects[1]);
                $teacher->addSubject($subjects[2]);
                $teacher->addSubject($subjects[4]);
            } elseif ($tData['specialty'] === 'Mathématiques') {
                $teacher->addSubject($subjects[0]);
            } else {
                $teacher->addSubject($subjects[3]);
            }
            
            $manager->persist($teacher);
            $teachers[] = $teacher;
        }

        // Create Classes
        $classes = [];
        $classData = [
            ['name' => '2INFO-A', 'level' => 'L2', 'field' => 'Informatique', 'capacity' => 30, 'year' => '2024-2025'],
            ['name' => '2INFO-B', 'level' => 'L2', 'field' => 'Informatique', 'capacity' => 30, 'year' => '2024-2025'],
            ['name' => '1INFO-A', 'level' => 'L1', 'field' => 'Informatique', 'capacity' => 35, 'year' => '2024-2025'],
        ];

        foreach ($classData as $cData) {
            $classe = new Classe();
            $classe->setName($cData['name']);
            $classe->setLevel($cData['level']);
            $classe->setField($cData['field']);
            $classe->setMaxCapacity($cData['capacity']);
            $classe->setAcademicYear($cData['year']);
            if (!empty($teachers)) {
                $classe->setSupervisor($teachers[0]);
            }
            $manager->persist($classe);
            $classes[] = $classe;
        }

        // Create Students
        $firstNames = ['Youssef', 'Aicha', 'Omar', 'Laila', 'Hassan', 'Sanae', 'Karim', 'Nadia'];
        $lastNames = ['Alami', 'Bennani', 'Chraibi', 'Dahbi', 'El Fassi', 'Fassi', 'Ghazi', 'Haddad'];
        
        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setEmail('student' . ($i + 1) . '@campusflow.com');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'student123'));
            $user->setRoles(['ROLE_STUDENT']);
            $user->setCreatedAt(new \DateTime());
            $manager->persist($user);

            $student = new Student();
            $student->setUser($user);
            $student->setFirstName($firstNames[$i % count($firstNames)]);
            $student->setLastName($lastNames[$i % count($lastNames)]);
            $student->setCne('CNE' . str_pad($i + 1, 6, '0', STR_PAD_LEFT));
            $student->setBirthDate(new \DateTime('2000-01-01'));
            $student->setPhone('06' . str_pad($i, 8, '0', STR_PAD_LEFT));
            $student->setAddress('Adresse ' . ($i + 1));
            $student->setClasse($classes[$i % count($classes)]);
            $student->setEnrollmentDate(new \DateTime());
            $student->setStatus('active');
            $manager->persist($student);
        }

        // Create Schedules
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $times = [
            ['start' => '08:00', 'end' => '10:00'],
            ['start' => '10:15', 'end' => '12:15'],
            ['start' => '14:00', 'end' => '16:00'],
            ['start' => '16:15', 'end' => '18:15'],
        ];
        $rooms = ['A101', 'A102', 'A201', 'A202', 'B101', 'B102'];
        $courseTypes = ['CM', 'TD', 'TP'];

        for ($i = 0; $i < 10; $i++) {
            $schedule = new Schedule();
            $schedule->setDayOfWeek($days[$i % count($days)]);
            $time = $times[$i % count($times)];
            $schedule->setStartTime(new \DateTime($time['start']));
            $schedule->setEndTime(new \DateTime($time['end']));
            $schedule->setSubject($subjects[$i % count($subjects)]);
            $schedule->setTeacher($teachers[$i % count($teachers)]);
            $schedule->setClasse($classes[$i % count($classes)]);
            $schedule->setRoom($rooms[$i % count($rooms)]);
            $schedule->setCourseType($courseTypes[$i % count($courseTypes)]);
            $schedule->setSemester(($i % 2) + 1);
            $manager->persist($schedule);
        }

        $manager->flush();
    }
}
