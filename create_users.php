<?php
// create_users.php (in project root: C:\symphonie\CampusFlow\)

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

// Get the entity manager (this one is still public)
$entityManager = $container->get('doctrine.orm.entity_manager');

// Get the password hasher via the interface (this works because Symfony supports autowiring by type)
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

$users = [
    ['username' => 'admin',     'password' => 'aa', 'roles' => ['ROLE_ADMIN']],
    ['username' => 'teacher1',  'password' => 'aa', 'roles' => ['ROLE_TEACHER']],
    ['username' => 'student1',  'password' => 'aa', 'roles' => ['ROLE_USER']],
];

foreach ($users as $data) {
    $user = new User();
    $user->setUsername($data['username']);
    $user->setEmail($data['username'] . '@campusflow.local'); // required field
    $user->setRoles($data['roles']);
    $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
    $user->setCreatedAt(new \DateTime());
    $user->setUpdatedat(new \DateTime()); // note: lowercase 't' as in your entity

    $entityManager->persist($user);
}

$entityManager->flush();

echo "Users created successfully!\n";