<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Classe;
use App\Entity\Eleve;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        /*
        =====================
        1️⃣ CREATE CLASSES
        =====================
        */
        $classesData = [
            ['nom' => '6A', 'niveau' => 'Sixième'],
            ['nom' => '5B', 'niveau' => 'Cinquième'],
            ['nom' => '4C', 'niveau' => 'Quatrième'],
        ];

        $classes = [];

        foreach ($classesData as $data) {
            $classe = new Classe();
            $classe->setNom($data['nom']);
            $classe->setNiveau($data['niveau']);

            $manager->persist($classe);
            $classes[] = $classe;
        }

        // 🔥 IMPORTANT: flush so UUIDs are generated
        $manager->flush();

        /*
        =====================
        2️⃣ CREATE USERS
        =====================
        */
        $usersData = [
            [
                'username' => 'admin',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'aa',
                'email' => 'admin@campusflow.local',
            ],
            [
                'username' => 'teacher1',
                'roles' => ['ROLE_TEACHER'],
                'password' => 'aa',
                'email' => 'teacher1@campusflow.local',
            ],
            [
                'username' => 'student1',
                'roles' => ['ROLE_USER'],
                'password' => 'aa',
                'email' => 'student1@campusflow.local',
            ],
            [
                'username' => 'student2',
                'roles' => ['ROLE_USER'],
                'password' => 'aa',
                'email' => 'student2@campusflow.local',
            ],
        ];

        $users = [];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setRoles($data['roles']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $data['password'])
            );

            $manager->persist($user);
            $users[$data['username']] = $user;
        }

        // 🔥 Flush users too (clean & safe)
        $manager->flush();

        /*
        =====================
        3️⃣ CREATE ÉLÈVES
        =====================
        */
        $elevesData = [
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'user' => 'student1',
                'classe' => 0,
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Alice',
                'user' => 'student2',
                'classe' => 1,
            ],
        ];

        foreach ($elevesData as $data) {
            $eleve = new Eleve();
            $eleve->setNom($data['nom']);
            $eleve->setPrenom($data['prenom']);
            $eleve->setDateNais(new \DateTime('2012-01-01'));
            $eleve->setAdresse('123 Rue de l\'École');
            $eleve->setNomParent('Parent ' . $data['nom']);
            $eleve->setTelParent('0600000000');

            // Relations (now safe!)
            $eleve->setUser($users[$data['user']]);
            $eleve->setClasse($classes[$data['classe']]);

            $manager->persist($eleve);
        }

        $manager->flush();
    }
}
