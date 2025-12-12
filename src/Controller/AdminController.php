<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ProductsRepository;
use App\Repository\ActivityLogRepository;
use App\Repository\ServicesRepository;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/system')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(UserRepository $userRepository, ProductsRepository $productsRepository, 
    ActivityLogRepository $activityLogRepository, ServicesRepository $servicesRepository, OrdersRepository $ordersRepository): Response
    {

        $roleCounts = $userRepository->getUserCountsByRole();

        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $userRepository->count([]),
            'total_products' => $productsRepository->count([]),
            'total_services' => $servicesRepository->count([]),
            'total_orders' => $ordersRepository->count([]),
            'role_counts' => $roleCounts,
            'total_active_serv' => $servicesRepository->count(['status' => 'Active']),
            'total_active' => $userRepository->count(['status' => 'Active']),
            'recent_logs' =>  $activityLogRepository->findRecentLogsWithUser(3),
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/account', name: 'app_admin_account')]
    public function account(): Response
    {
        return $this->render('admin/account.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/account/update', name: 'app_admin_account_update', methods: ['POST'])]
    public function updateAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin_account');
        }
        
        if ($request->request->has('update_profile')) {
            // Update username
            $newUsername = trim($request->request->get('username', ''));
            
            if (empty($newUsername)) {
                $this->addFlash('error', 'Username cannot be empty.');
                return $this->redirectToRoute('app_admin_account');
            }
            
            // Check if username has changed
            if ($newUsername !== $user->getUsername()) {
                // Check if username already exists (optional - unique constraint will catch it anyway)
                $user->setUsername($newUsername);
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Username updated successfully!');
            } else {
                $this->addFlash('info', 'Username unchanged.');
            }
        }
        
        if ($request->request->has('change_password')) {
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // Check if new passwords match
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'New passwords do not match.');
                return $this->redirectToRoute('app_admin_account');
            }
            
            // Check if password meets minimum length
            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters long.');
                return $this->redirectToRoute('app_admin_account');
            }
            
            // Check if new password is the same as current (optional - you can remove this if you want)
            if ($passwordHasher->isPasswordValid($user, $newPassword)) {
                $this->addFlash('error', 'New password must be different from current password.');
                return $this->redirectToRoute('app_admin_account');
            }
            
            // Update password
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Password changed successfully!');
        }
        
        return $this->redirectToRoute('app_admin_account');
    }
}