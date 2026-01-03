<?php

namespace App\Controller\Teacher;

use App\Form\TeacherProfileType;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teacher', name: 'teacher_')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(
        Request $request,
        TeacherRepository $teacherRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $user = $this->getUser();
        $teacher = $teacherRepository->findOneBy(['user' => $user]);

        if (!$teacher) {
            throw $this->createNotFoundException('Profil enseignant non trouvé');
        }

        $form = $this->createForm(TeacherProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashed = $passwordHasher->hashPassword($user, $plainPassword);
                /** @var \App\Entity\User $user */
                $user->setPassword($hashed);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('teacher_profile');
        }

        return $this->render('teacher/profile.html.twig', [
            'teacher' => $teacher,
            'form' => $form->createView(),
        ]);
    }
}
