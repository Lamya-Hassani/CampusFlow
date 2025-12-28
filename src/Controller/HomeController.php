<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Si l'utilisateur est connecté, rediriger vers son dashboard
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('admin_dashboard');
            } elseif (in_array('ROLE_TEACHER', $roles)) {
                return $this->redirectToRoute('teacher_dashboard');
            } elseif (in_array('ROLE_STUDENT', $roles)) {
                return $this->redirectToRoute('student_dashboard');
            }
        }

        // Sinon, afficher la landing page
        return $this->render('home/index.html.twig');
    }
}