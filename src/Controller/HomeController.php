<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    #[Route('/home_user', name: 'app_home_user')]
    public function index(): Response
    {
        return $this->render('home_user/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
