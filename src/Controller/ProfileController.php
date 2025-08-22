<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    private function assertOwnerOrThrow(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $user;
    }

    private function back(Request $request): Response
    {
        $ref = $request->headers->get('referer');
        return $ref ? $this->redirect($ref) : $this->redirectToRoute('app_home');
    }

    #[Route('/avatar', name: 'profile_update_avatar', methods: ['POST'])]
    public function updateAvatar(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();

        if (!$this->isCsrfTokenValid('update_avatar', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('avatar');
        if ($file instanceof UploadedFile && $file->isValid()) {
            $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/avatars';
            if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: $file->guessExtension() ?: 'jpg';
            $newName = bin2hex(random_bytes(12)).'.'.$ext;
            $file->move($targetDir, $newName);

            $user->setAvatar($newName);
            $em->flush();
            $this->addFlash('success', 'Photo de profil mise à jour.');
        } else {
            $this->addFlash('error', 'Fichier invalide.');
        }

        return $this->back($request);
    }

    #[Route('/about', name: 'profile_update_about', methods: ['POST'])]
    public function updateAbout(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();
        if (!$this->isCsrfTokenValid('profile_update_about', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        $about = trim((string)$request->request->get('about', ''));
        $user->setAbout($about);
        $em->flush();
        $this->addFlash('success', 'À propos mis à jour.');
        return $this->back($request);
    }

    #[Route('/interests', name: 'profile_update_interests', methods: ['POST'])]
    public function updateInterests(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();
        if (!$this->isCsrfTokenValid('profile_update_interests', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        $raw = (string)$request->request->get('interests', '');
        $list = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)), fn($v) => $v !== '')));
        $user->setInterests($list);
        $em->flush();
        $this->addFlash('success', 'Centres d’intérêt mis à jour.');
        return $this->back($request);
    }

    #[Route('/core', name: 'profile_update_core', methods: ['POST'])]
    public function updateCore(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();
        if (!$this->isCsrfTokenValid('profile_update_core', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        $name    = (string)$request->request->get('name', $user->getName() ?? '');
        $age     = $request->request->get('age');
        $city    = (string)$request->request->get('city', $user->getCity() ?? '');
        $country = (string)$request->request->get('country', $user->getCountry() ?? '');

        $user->setName($name);
        $user->setCity($city);
        $user->setCountry($country);
        if ($age !== null && $age !== '') {
            $user->setAge((int)$age);
        }

        $em->flush();
        $this->addFlash('success', 'Informations mises à jour.');
        return $this->back($request);
    }

    #[Route('/badge/add', name: 'profile_badge_add', methods: ['POST'])]
    public function addBadge(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();
        if (!$this->isCsrfTokenValid('badge_add', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        $badge = trim((string)$request->request->get('badge', ''));
        if ($badge !== '') {
            $badges = $user->getBadges();
            if (!in_array($badge, $badges, true)) {
                $badges[] = $badge;
                $user->setBadges($badges);
                $em->flush();
                $this->addFlash('success', 'Badge ajouté.');
            }
        }
        return $this->back($request);
    }

    #[Route('/gallery/add', name: 'profile_gallery_add', methods: ['POST'])]
    public function addGallery(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->assertOwnerOrThrow();
        if (!$this->isCsrfTokenValid('gallery_add', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'CSRF invalide.');
            return $this->back($request);
        }

        $files = $request->files->all('photos') ?: $request->files->get('photos', []);
        if (!is_array($files)) { $files = [$files]; }

        $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/gallery';
        if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

        $gallery = $user->getGallery();
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) continue;
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: $file->guessExtension() ?: 'jpg';
            $newName = bin2hex(random_bytes(12)).'.'.$ext;
            $file->move($targetDir, $newName);
            $gallery[] = $newName;
        }
        $user->setGallery($gallery);
        $em->flush();

        $this->addFlash('success', 'Photos ajoutées.');
        return $this->back($request);
    }

    #[Route('/message/new/{to}', name: 'app_message_new', methods: ['GET'])]
public function messageNew(User $to, Request $request): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    // TODO: à remplacer par ta vraie page de création de conversation
    $this->addFlash('info', sprintf('Messagerie bientôt disponible avec %s.', $to->getName() ?? 'cet utilisateur'));
    $ref = $request->headers->get('referer');
    return $ref ? $this->redirect($ref) : $this->redirectToRoute('app_home');
}

#[Route('/favorite/toggle/{id}', name: 'app_favorite_toggle', methods: ['GET','POST'])]
public function favoriteToggle(User $user, Request $request): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    // TODO: persister un vrai "favori" (ex: table favorites ou JSON dans User)
    $this->addFlash('info', sprintf('Favoris bientôt disponible pour %s.', $user->getName() ?? 'cet utilisateur'));
    $ref = $request->headers->get('referer');
    return $ref ? $this->redirect($ref) : $this->redirectToRoute('app_home');
}
}
