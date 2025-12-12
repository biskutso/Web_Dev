<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Services;
use App\Entity\User;
use App\Form\OrdersType;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ActivityLogger;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/orders')]
final class OrdersController extends AbstractController
{
    #[Route(name: 'app_orders_index', methods: ['GET'])]
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('orders/index.html.twig', [
            'orders' => $ordersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_orders_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('You do not have permission to create orders.');
        }
        
        $order = new Orders();
        $currentUser = $this->getUser();
        
        // Set the current user
        if ($currentUser instanceof User) {
            $order->setUser($currentUser);
            $order->setCreatedBy($currentUser);
        }
        
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle user selection for admin/staff
            if (($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) && $request->request->has('selected_user')) {
                $selectedUserId = $request->request->get('selected_user');
                if ($selectedUserId) {
                    $selectedUser = $entityManager->getRepository(User::class)->find($selectedUserId);
                    if ($selectedUser) {
                        $order->setUser($selectedUser);
                    }
                }
            }
            
            // Handle product quantity reduction
            $product = $order->getProductId();
            if ($product && $product->getQuantity() > 0) {
                $product->setQuantity($product->getQuantity() - 1);
                $entityManager->persist($product);
            }
            
            $entityManager->persist($order);
            $entityManager->flush();

            // Get details for logging
            $productName = $product ? $product->getName() : 'None';
            $serviceName = $order->getServiceId() ? $order->getServiceId()->getName() : 'None';
            $orderUser = $order->getUser();
            $userName = $orderUser ? $orderUser->getUserIdentifier() : 'Unknown';
            $createdByName = $order->getCreatedBy() ? $order->getCreatedBy()->getUserIdentifier() : 'Unknown';

            // Use standardized action names
            $activitylogger->log(
                'Created Order',
                'Order ID: ' . $order->getId() . 
                ' | Product: ' . $productName .
                ' | Service: ' . $serviceName .
                ' | User: ' . $userName .
                ' | Created By: ' . $createdByName
            );

            $this->addFlash('success', 'Order created successfully!');
            return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        // Get available products and services
        $products = $entityManager->getRepository(Products::class)
            ->createQueryBuilder('p')
            ->where('p.quantity > 0')
            ->getQuery()
            ->getResult();
        
        $services = $entityManager->getRepository(Services::class)->findAll();
        
        // Get ROLE_USER users for admin/staff
        $users = [];
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
            $users = $entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%ROLE_USER%')
                ->orderBy('u.username', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('orders/new.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'services' => $services,
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'app_orders_show', methods: ['GET'])]
    public function show(Orders $order): Response
    {
        $this->checkOrderAccess($order, 'view');
        
        return $this->render('orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_orders_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Orders $order, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkOrderAccess($order, 'edit');
        
        // Store original product before form changes
        $originalProduct = $order->getProductId();
        $originalService = $order->getServiceId();
        
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        // Get all available products and services for display
        $products = $entityManager->getRepository(Products::class)->findAll();
        $services = $entityManager->getRepository(Services::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle inventory changes
            $newProduct = $order->getProductId();
            $newService = $order->getServiceId();
            
            $changes = [];
            
            // Changing from product to different product
            if ($originalProduct && $newProduct && $originalProduct->getId() !== $newProduct->getId()) {
                // Return quantity to original product
                $originalProduct->setQuantity($originalProduct->getQuantity() + 1);
                $entityManager->persist($originalProduct);
                $changes[] = 'Returned product: ' . $originalProduct->getName();
                
                // Remove quantity from new product
                if ($newProduct->getQuantity() > 0) {
                    $newProduct->setQuantity($newProduct->getQuantity() - 1);
                    $entityManager->persist($newProduct);
                    $changes[] = 'Added product: ' . $newProduct->getName();
                }
            }
            // Changing from product to service
            elseif ($originalProduct && $newService) {
                // Return quantity to original product
                $originalProduct->setQuantity($originalProduct->getQuantity() + 1);
                $entityManager->persist($originalProduct);
                $changes[] = 'Returned product: ' . $originalProduct->getName();
                $changes[] = 'Added service: ' . $newService->getName();
            }
            // Changing from service to product
            elseif ($originalService && $newProduct) {
                // Remove quantity from new product
                if ($newProduct->getQuantity() > 0) {
                    $newProduct->setQuantity($newProduct->getQuantity() - 1);
                    $entityManager->persist($newProduct);
                    $changes[] = 'Added product: ' . $newProduct->getName();
                }
                $changes[] = 'Removed service: ' . $originalService->getName();
            }
            
            $entityManager->flush();
            
            // Log the edit action
            $currentUser = $this->getUser();
            $currentUserName = $currentUser ? $currentUser->getUserIdentifier() : 'Unknown';
            
            // Use standardized action names
            $activitylogger->log(
                'Edited Order',
                'Order ID: ' . $order->getId() . 
                ' | Updated by: ' . $currentUserName .
                ($changes ? ' | Changes: ' . implode(', ', $changes) : '')
            );

            $this->addFlash('success', 'Order updated successfully! Inventory adjusted accordingly.');
            return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'products' => $products,
            'services' => $services,
            'originalProduct' => $originalProduct,
            'originalService' => $originalService,
        ]);
    }

    #[Route('/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkOrderAccess($order, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            // Store order details before deletion for logging
            $orderId = $order->getId();
            $orderUser = $order->getUser();
            $userName = $orderUser ? $orderUser->getUserIdentifier() : 'Unknown';
            $product = $order->getProductId() ? $order->getProductId()->getName() : 'None';
            $service = $order->getServiceId() ? $order->getServiceId()->getName() : 'None';
            
            // Return product quantity if order had a product
            $productEntity = $order->getProductId();
            if ($productEntity) {
                $productEntity->setQuantity($productEntity->getQuantity() + 1);
                $entityManager->persist($productEntity);
            }
            
            $entityManager->remove($order);
            $entityManager->flush();
            
            // Get current user
            $currentUser = $this->getUser();
            $currentUserName = $currentUser ? $currentUser->getUserIdentifier() : 'Unknown';
            
            // Use standardized action names
            $activitylogger->log(
                'Deleted Order',
                'Order ID: ' . $orderId . 
                ' | Product: ' . $product . 
                ' | Service: ' . $service .
                ' | User: ' . $userName .
                ' | Deleted by: ' . $currentUserName
            );

            $this->addFlash('success', 'Order deleted successfully!');
        }

        return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkOrderAccess(Orders $order, string $action = 'view'): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        $currentUser = $this->getUser();
        $orderUser = $order->getUser();
        
        if ($this->isGranted('ROLE_STAFF')) {
            if ($action === 'view') {
                return;
            }
            
            if ($action === 'edit' || $action === 'delete') {
                if (!$currentUser || !$orderUser) {
                    throw new AccessDeniedException('You do not have permission to ' . $action . ' this order.');
                }
                return;
            }
        }

        if ($this->isGranted('ROLE_USER')) {
            if (!$currentUser || !$orderUser) {
                throw new AccessDeniedException('You can only ' . $action . ' your own orders.');
            }
            
            if ($currentUser === $orderUser) {
                return;
            }
            
            throw new AccessDeniedException('You can only ' . $action . ' your own orders.');
        }

        throw new AccessDeniedException('You do not have permission to ' . $action . ' orders.');
    }
}