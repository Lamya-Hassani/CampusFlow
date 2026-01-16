<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Teacher;
use App\Entity\Student;
use App\Entity\Classe;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Entity\Grade;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // -------------------------
        // Helpers
        // -------------------------
        $mkEmail = fn(string $first, string $last) =>
            strtolower($first.'.'.$last).'@campusflow.com';

        $mkUser = function(string $first, string $last, array $roles) use ($mkEmail) {
            $u = new User();
            $u->setEmail($mkEmail($first, $last));
            $u->setRoles($roles);
            $u->setCreatedAt(new \DateTimeImmutable('2025-01-05 09:00:00'));

            $u->setPassword($this->hasher->hashPassword($u, 'azsq'));
            return $u;
        };

        // -------------------------
        // Subjects (20)
        // -------------------------
        $subjects = [];
        $subjectData = [
            ['Analyse 1','AN1',3.0], ['Algèbre 1','ALG1',3.0], ['Programmation 1','PRG1',4.0],
            ['Structures de données','SD1',4.0], ['BD 1','BD1',3.5], ['Réseaux 1','NET1',3.0],
            ['Systèmes 1','SYS1',3.0], ['Web 1','WEB1',3.0], ['Génie logiciel','GL1',3.0],
            ['IA 1','AI1',3.0], ['Sécurité','SEC1',3.0], ['DevOps','DVO1',2.0],
            ['Maths discrètes','MD1',3.0], ['Architecture','ARC1',2.5], ['Python','PY1',3.0],
            ['Projet','PRJ1',4.5], ['Probabilités','PROB1',3.0], ['BD 2','BD2',3.5],
            ['Web 2','WEB2',3.5], ['Réseaux 2','NET2',3.0],
        ];

        foreach ($subjectData as [$name,$code,$coef]) {
            $s = new Subject();
            $s->setName($name);
            $s->setCode($code);
            $s->setCoefficient((string)$coef);
            $s->setDescription('Fixture data');
            $manager->persist($s);
            $subjects[] = $s;
        }

        // -------------------------
        // Users + Teachers (10)
        // Each teacher teaches exactly 2 subjects
        // -------------------------
        $teachers = [];
        $teacherPeople = [
            ['Yassine','ElAmrani','Informatique','Professeur'],
            ['Salma','Berrada','Mathématiques','Maître de conférences'],
            ['Omar','Raji','Réseaux','Professeur'],
            ['Hind','Lahlou','Base de données','Professeur'],
            ['Nabil','Sefrioui','Programmation','Professeur'],
            ['Imane','Toumi','IA','Professeur'],
            ['Anas','Mourad','Sécurité','Professeur'],
            ['Kawtar','Idrissi','Web','Professeur'],
            ['Mehdi','Bennis','Systèmes','Professeur'],
            ['Aya','Chakir','Algorithmique','Professeur'],
        ];

        foreach ($teacherPeople as $i => [$first,$last,$spec,$gradeLabel]) {
            $u = $mkUser($first, $last, ['ROLE_TEACHER']);
            $manager->persist($u);

            $t = new Teacher();
            $t->setUser($u);
            $t->setFirstName($first);
            $t->setLastName($last);
            $t->setPhone('0600000'.str_pad((string)($i+101), 3, '0', STR_PAD_LEFT));
            $t->setSpecialty($spec);
            $t->setGrade($gradeLabel);

            // assign 2 subjects each (2*i and 2*i+1)
            $t->addSubject($subjects[$i*2]);
            $t->addSubject($subjects[$i*2 + 1]);

            $manager->persist($t);
            $teachers[] = $t;
        }

        // -------------------------
        // Admin user
        // -------------------------
        $admin = $mkUser('Admin', 'CampusFlow', ['ROLE_ADMIN']);
        $manager->persist($admin);

        // -------------------------
        // Classes (6) - each supervised by a teacher
        // -------------------------
        $classes = [];
        $classData = [
            ['L1-INFO-A','L1','Informatique',35,'2025-2026', 0],
            ['L1-INFO-B','L1','Informatique',35,'2025-2026', 1],
            ['L2-INFO-A','L2','Informatique',30,'2025-2026', 2],
            ['L2-INFO-B','L2','Informatique',30,'2025-2026', 3],
            ['L3-INFO-A','L3','Informatique',28,'2025-2026', 4],
            ['L3-INFO-B','L3','Informatique',28,'2025-2026', 5],
        ];

        foreach ($classData as [$name,$level,$field,$cap,$year,$supervisorIndex]) {
            $c = new Classe();
            $c->setName($name);
            $c->setLevel($level);
            $c->setField($field);
            $c->setMaxCapacity($cap);
            $c->setAcademicYear($year);
            $c->setSupervisor($teachers[$supervisorIndex]); // FK supervisor_id -> teacher.id
            $manager->persist($c);
            $classes[] = $c;
        }

        // -------------------------
        // Students (15 per class => 90)
        // -------------------------
        $students = [];
        $firstNames = ['Imad','Sara','Yasmine','Hamza','Aya','Ilyas','Nour','Rania','Walid','Lina','Oussama','Salma','Hajar','Ziad','Meriem'];
        $lastNames  = ['Amrani','Haddou','Bouzid','Rami','Laziri','Bakkali','Fassi','Alaoui','Saidi','Zerhouni','ElKadi','Benjelloun','Chraibi','Tazi','Bennani'];

        $studentCounter = 1;
        foreach ($classes as $classIndex => $classe) {
            for ($j=0; $j<15; $j++) {
                $first = $firstNames[$j];
                $last  = $lastNames[$j]; // removed numbering

                $u = $mkUser($first, $last, ['ROLE_STUDENT']);
                $u->setEmail($mkEmail($first, $last . $studentCounter)); // Unique email by using counter instead of last name
                $manager->persist($u);

                $st = new Student();
                $st->setUser($u);
                $st->setClasse($classe);
                $st->setFirstName($first);
                $st->setLastName($last);
                $st->setCne('CNE'.str_pad((string)$studentCounter, 8, '0', STR_PAD_LEFT));
                $st->setBirthDate(new \DateTimeImmutable('2006-01-01'));
                $st->setEnrollmentDate(new \DateTimeImmutable('2025-09-15'));
                $st->setStatus('active');
                $st->setPhone('06010'.str_pad((string)$studentCounter, 5, '0', STR_PAD_LEFT));
                $st->setAddress('Casablanca');

                $manager->persist($st);
                $students[] = $st;
                $studentCounter++;
            }
        }

        // -------------------------
        // Schedule: build a "full" week for each teacher
        // Each teacher teaches to 2 different classes (rotation)
        // -------------------------
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        $slots = [
            ['08:00:00','10:00:00'], ['10:15:00','12:15:00'],
            ['14:00:00','16:00:00'], ['16:15:00','18:15:00'],
        ];
        $rooms = ['A101','A102','A201','A202','B101','B102'];
        $courseTypes = ['CM','TD','TP'];

        // -------------------------
        // Schedule: Deterministic grid to avoid ALL overlaps
        // 5 days, 4 slots = 20 global slots
        // Total requirements: 10 teachers * 10 sessions = 100 sessions total.
        // Capacity per slot: min(10 teachers, 6 classes, 6 rooms) = 6 sessions max per slot.
        // Total capacity: 20 slots * 6 sessions = 120 sessions.
        // -------------------------
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        $slots = [
            ['08:00:00','10:00:00'], ['10:15:00','12:15:00'],
            ['14:00:00','16:00:00'], ['16:15:00','18:15:00'],
        ];
        $rooms = ['A101','A102','A201','A202','B101','B102'];
        $courseTypes = ['CM','TD','TP'];

        // We will distribute the 100 sessions across the 120 available "class-room" slots.
        // For each of the 20 time slots (day/hour), we can have at most 6 concurrent sessions.
        $sessionCount = 0;
        for ($d = 0; $d < 5; $d++) {
            for ($s = 0; $s < 4; $s++) {
                // For each time slot, try to fill up to 6 parallel sessions (one per class/room)
                for ($p = 0; $p < 6; $p++) {
                    if ($sessionCount >= 100) break;

                    // Teacher: rotates through all 10 teachers
                    $teacherIndex = $sessionCount % 10;
                    $teacher = $teachers[$teacherIndex];
                    
                    // Room: unique for this slot because we iterate $p from 0 to 5
                    $room = $rooms[$p];
                    
                    // Class: unique for this slot because we iterate $p from 0 to 5
                    // We can rotate which class gets which room/teacher over time
                    $class = $classes[$p];

                    $subA = $teacher->getSubjects()->first();
                    $subB = $teacher->getSubjects()->last();

                    $sch = new Schedule();
                    $sch->setTeacher($teacher);
                    $sch->setSubject(($sessionCount % 2 === 0) ? $subA : $subB);
                    $sch->setClasse($class);
                    $sch->setDayOfWeek($days[$d]);
                    $sch->setStartTime(new \DateTimeImmutable($slots[$s][0]));
                    $sch->setEndTime(new \DateTimeImmutable($slots[$s][1]));
                    $sch->setRoom($room);
                    $sch->setCourseType($courseTypes[$sessionCount % 3]);
                    $sch->setSemester(1);

                    $manager->persist($sch);
                    $sessionCount++;
                }
            }
        }

        // -------------------------
        // Grades: distributed over 2025 and 2026
        // For each student: 2 subjects of their class supervisor teacher (demo)
        // -------------------------
        $gradeTypes = ['control','tp','exam'];
        $dates = [
            '2025-10-10 09:00:00', '2025-11-15 09:00:00', '2026-01-20 09:00:00',
            '2026-03-10 09:00:00', '2026-05-25 09:00:00',
        ];

        foreach ($classes as $classe) {
            $supervisor = $classe->getSupervisor();
            $sub1 = $supervisor->getSubjects()->first();
            $sub2 = $supervisor->getSubjects()->last();

            foreach ($students as $st) {
                if ($st->getClasse() !== $classe) continue;

                foreach ([$sub1,$sub2] as $sub) {
                    foreach ($gradeTypes as $gIndex => $type) {
                        $g = new Grade();
                        $g->setStudent($st);
                        $g->setClasse($classe);
                        $g->setTeacher($supervisor);
                        $g->setSubject($sub);

                        $g->setType($type);
                        $g->setSemester(($gIndex < 2) ? 1 : 2);
                        // createdAt is auto-set in Grade constructor
                        $g->setValue((string) (10 + (($st->getId() + $gIndex) % 11) + 0.25)); // 10.25 .. 20.25-ish

                        $manager->persist($g);
                    }
                }
            }
        }

        $manager->flush();
    }
}
