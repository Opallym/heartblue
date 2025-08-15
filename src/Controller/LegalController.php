<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/politique-de-confidentialite', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('privacy_policy/privacy_policy.html.twig');
    }
}