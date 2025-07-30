<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/activity')]
final class ActivityController extends AbstractController
{
    #[Route(name: 'app_activity_index', methods: ['GET'])]
    public function index(ActivityRepository $activityRepository): Response
    {
        return $this->render('activity/index.html.twig', [
            'activities' => $activityRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_activity_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setCreatedBy($this->getUser());

            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activity_show', methods: ['GET'])]
    public function show(Activity $activity): Response
    {
        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_activity_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activity_delete', methods: ['POST'])]
    public function delete(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour supprimer une activité.');
        }

        if ($activity->getCreatedBy() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette activité.');
        }

        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $entityManager->remove($activity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
    }
}
