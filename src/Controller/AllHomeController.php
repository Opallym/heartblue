<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AllHomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
public function index(): Response
{
    $testimonials = [
        "Super site, j'ai rencontré plein de gens !",
        "Activités variées et très bien organisées.",
        "L'interface est intuitive, j'adore.",
        "J'ai pu créer mon propre événement facilement.",
        "Merci Hearth Blue pour cette belle communauté !",
        "J'apprécie la modération et la bienveillance.",
        "Les fonctionnalités sont vraiment complètes.",
        "Je recommande à tous ceux qui veulent sortir !",
        "Le service client est très réactif.",
        "Une belle initiative locale qui fait du bien.",
        "5 étoiles sans hésiter.",
        "Plusieurs amis m'ont rejoint grâce à Hearth Blue.",
        "Chaque activité est une vraie réussite.",
        "Très facile à utiliser sur mobile.",
        "Un site convivial et efficace.",
        "Des idées d'activités toujours originales.",
        "Parfait pour découvrir sa région autrement.",
        "Le système d'inscription est simple et rapide.",
        "J'adore les notifications personnalisées.",
        "La communauté est dynamique et accueillante."
    ];

    return $this->render('home/index.html.twig', [
        'testimonials' => $testimonials,
    ]);
}

}
