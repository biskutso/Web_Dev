<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ActivityLogger;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ActivityLogger $activitylogger): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_new' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password for new user
            $plainPassword = $form->get('password')->getData();
            
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Created User',
                'User: ' . $user->getUsername() . ' Role: ' . str_replace('ROLE_', '', $user->getPrimaryRole()) . ' (ID:' . $user->getId() . ')'
            );

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ActivityLogger $activitylogger): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'is_new' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password for edit - only update if provided
            $plainPassword = $form->get('password')->getData();
            
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Edited User',
                'User: ' . $user->getUsername() . ' Role: ' . str_replace('ROLE_', '', $user->getPrimaryRole()) . ' (ID:' . $user->getId() . ')'
            );

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $userId = $user->getId();
            $userName = $user->getUsername();
            $userRole = str_replace('ROLE_', '', $user->getPrimaryRole());
           
            $entityManager->remove($user);
            $entityManager->flush();
            
            // Use standardized action names
            $activitylogger->log(
                'Deleted User',
                'User: ' . $userName . ' Role: ' . $userRole . ' (ID:' . $userId . ')'
            );

            $this->addFlash('success', 'User deleted successfully.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}