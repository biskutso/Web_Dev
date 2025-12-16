<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Services;
use App\Entity\User;
use App\Form\OrdersType;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\ServicesRepository;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/orders')]
class OrdersController extends AbstractController
{
    #[Route('/', name: 'app_orders_index', methods: ['GET'])]
    public function index(OrdersRepository $ordersRepository): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get orders based on user role
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
            // BOTH Admin and Staff can see all orders
            $orders = $ordersRepository->findAll();
        } elseif ($this->isGranted('ROLE_STAFF')) {
            // This line is kept for backward compatibility but won't be reached due to above condition
            $orders = $ordersRepository->findBy(['createdBy' => $this->getUser()]);
        } else {
            $orders = $ordersRepository->findBy(['user' => $this->getUser()]);
        }

        return $this->render('orders/index.html.twig', [
            'orders' => array_map(function (Orders $order) {
                return [
                    'id' => $order->getId(),

                    // USER - use snapshot if user is deleted
                    'user' => $order->getUser()
                        ? $order->getUser()->getUsername()
                        : ($order->getUserNameSnapshot() ?: 'Unknown User'),
                    
                    // Pass snapshot for display
                    'userNameSnapshot' => $order->getUserNameSnapshot(),

                    // CREATED BY
                    'createdBy' => $order->getCreatedBy()
                        ? $order->getCreatedBy()->getUsername()
                        : 'N/A',

                    // PRODUCT (use snapshot if product is deleted)
                    'product' => $order->getProductId()
                        ? $order->getProductId()->getName()
                        : null,
                    
                    // Pass snapshot for display
                    'productNameSnapshot' => $order->getProductNameSnapshot(),

                    // SERVICE (use snapshot if service is deleted)
                    'service' => $order->getServiceId()
                        ? $order->getServiceId()->getName()
                        : null,
                    
                    // Pass snapshot for display
                    'serviceNameSnapshot' => $order->getServiceNameSnapshot(),

                    // CATEGORY (use snapshot if category is deleted)
                    'category' => $order->getCategoryId()
                        ? $order->getCategoryId()->getCategoryName()
                        : null,
                    
                    // Pass snapshot for display
                    'categoryNameSnapshot' => $order->getCategoryNameSnapshot(),

                    'price' => $order->getPrice(),
                    'quantity' => $order->getQuantity(),
                    'orderCreated' => $order->getOrderCreated(),
                ];
            }, $orders),
        ]);
    }

    #[Route('/new', name: 'app_orders_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ProductsRepository $productsRepository,
        ServicesRepository $servicesRepository,
        UserRepository $userRepository,
        ActivityLogger $activityLogger
    ): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $order = new Orders();
        
        // Get available products (only those with quantity > 0)
        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.quantity > 0')
            ->getQuery()
            ->getResult();
        
        $services = $servicesRepository->findAll();
        
        // Get users (for admin/staff selection)
        $users = [];
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
            $users = $userRepository->findBy([], ['username' => 'ASC']);
        }
        
        // Create form
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Handle user selection
                if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
                    $selectedUserId = $request->request->get('selected_user');
                    if ($selectedUserId) {
                        $selectedUser = $userRepository->find($selectedUserId);
                        if ($selectedUser) {
                            $order->setUser($selectedUser);
                        } else {
                            $order->setUser($this->getUser());
                        }
                    } else {
                        $order->setUser($this->getUser());
                    }
                } else {
                    // Regular users order for themselves
                    $order->setUser($this->getUser());
                }
                
                // Set created by
                $order->setCreatedBy($this->getUser());
                
                // Handle item selection
                $itemType = $form->get('itemType')->getData();
                $itemId = $form->get('selectedItem')->getData();
                
                if ($itemType === 'product' && $itemId) {
                    $product = $productsRepository->find($itemId);
                    if ($product) {
                        $order->setProductId($product);
                        $order->setPrice($product->getPrice());
                        // Set category from product
                        if ($product->getCategory()) {
                            $order->setCategoryId($product->getCategory());
                        }
                        // Update product quantity
                        $product->setQuantity($product->getQuantity() - 1);
                        $em->persist($product);
                    }
                } elseif ($itemType === 'service' && $itemId) {
                    $service = $servicesRepository->find($itemId);
                    if ($service) {
                        $order->setServiceId($service);
                        $order->setPrice($service->getPrice());
                        // Set category from service
                        if ($service->getCategory()) {
                            $order->setCategoryId($service->getCategory());
                        }
                    }
                }
                
                // Set snapshot data
                $order->setProductNameSnapshot($order->getProductId() ? $order->getProductId()->getName() : null);
                $order->setServiceNameSnapshot($order->getServiceId() ? $order->getServiceId()->getName() : null);
                $order->setCategoryNameSnapshot($order->getCategoryId() ? $order->getCategoryId()->getCategoryName() : null);
                $order->setUserNameSnapshot($order->getUser() ? $order->getUser()->getUsername() : null);
                
                $em->persist($order);
                $em->flush();
                
                // Log the activity
                $activityLogger->log(
                    'Created Order',
                    'Order #' . $order->getId() . 
                    ' | Customer: ' . ($order->getUser() ? $order->getUser()->getUsername() : 'N/A') .
                    ' | Item: ' . ($order->getProductId() ? $order->getProductId()->getName() : ($order->getServiceId() ? $order->getServiceId()->getName() : 'N/A')) .
                    ' | Price: ₱' . number_format($order->getPrice(), 2)
                );
                
                $this->addFlash('success', 'Order created successfully!');
                return $this->redirectToRoute('app_orders_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating order: ' . $e->getMessage());
            }
        }
        
        return $this->render('orders/new.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'services' => $services,
            'users' => $users,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_orders_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Orders $order, 
        EntityManagerInterface $em,
        ProductsRepository $productsRepository,
        ServicesRepository $servicesRepository,
        ActivityLogger $activityLogger
    ): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to edit this order
        $this->checkOrderAccess($order, 'edit');
        
        // Get available products and services
        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.quantity > 0')
            ->getQuery()
            ->getResult();
        
        $services = $servicesRepository->findAll();
        
        // Store original order details for logging
        $originalItem = $order->getProductId() 
            ? ($order->getProductId()->getName() . ' (Product)')
            : ($order->getServiceId() 
                ? ($order->getServiceId()->getName() . ' (Service)') 
                : 'No Item');
        $originalPrice = $order->getPrice();
        
        // Create the form using your existing OrdersType
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get the submitted data
                $selectedItemId = $form->get('selectedItem')->getData();
                $itemType = $form->get('itemType')->getData();
                
                // Store original product for quantity restoration
                $originalProduct = $order->getProductId();
                $originalService = $order->getServiceId();
                
                // Handle item selection from cards
                if ($selectedItemId && $itemType) {
                    if ($itemType === 'product') {
                        $product = $productsRepository->find($selectedItemId);
                        if ($product) {
                            // Restore original product quantity if changing from product
                            if ($originalProduct && $originalProduct->getId() != $product->getId()) {
                                $originalProduct->setQuantity($originalProduct->getQuantity() + $order->getQuantity());
                                $em->persist($originalProduct);
                            }
                            
                            // Set new product and reduce its quantity
                            $order->setProductId($product);
                            $order->setServiceId(null);
                            $product->setQuantity($product->getQuantity() - $order->getQuantity());
                            $em->persist($product);
                            
                            if ($product->getCategory()) {
                                $order->setCategoryId($product->getCategory());
                            }
                            
                            $order->setPrice($product->getPrice());
                        }
                    } elseif ($itemType === 'service') {
                        $service = $servicesRepository->find($selectedItemId);
                        if ($service) {
                            // Restore original product quantity if changing from product to service
                            if ($originalProduct) {
                                $originalProduct->setQuantity($originalProduct->getQuantity() + $order->getQuantity());
                                $em->persist($originalProduct);
                            }
                            
                            // Set new service
                            $order->setServiceId($service);
                            $order->setProductId(null);
                            
                            if ($service->getCategory()) {
                                $order->setCategoryId($service->getCategory());
                            }
                            
                            $order->setPrice($service->getPrice());
                        }
                    }
                }
                
                // Update snapshot data
                $order->setProductNameSnapshot($order->getProductId() ? $order->getProductId()->getName() : null);
                $order->setServiceNameSnapshot($order->getServiceId() ? $order->getServiceId()->getName() : null);
                $order->setCategoryNameSnapshot($order->getCategoryId() ? $order->getCategoryId()->getCategoryName() : null);
                
                $em->flush();
                
                // Get new order details for logging
                $newItem = $order->getProductId() 
                    ? ($order->getProductId()->getName() . ' (Product)')
                    : ($order->getServiceId() 
                        ? ($order->getServiceId()->getName() . ' (Service)') 
                        : 'No Item');
                $newPrice = $order->getPrice();
                
                // Log the activity
                $activityLogger->log(
                    'Edited Order',
                    'Order #' . $order->getId() . 
                    ' | Customer: ' . ($order->getUser() ? $order->getUser()->getUsername() : 'N/A') .
                    ' | Changed from: ' . $originalItem . ' (₱' . number_format($originalPrice, 2) . ')' .
                    ' | To: ' . $newItem . ' (₱' . number_format($newPrice, 2) . ')'
                );
                
                $this->addFlash('success', 'Order updated successfully!');
                return $this->redirectToRoute('app_orders_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error updating order: ' . $e->getMessage());
            }
        }
        
        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'products' => $products,
            'services' => $services,
        ]);
    }

    #[Route('/{id}', name: 'app_orders_show', methods: ['GET'])]
    public function show(Orders $order): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to view this order
        $this->checkOrderAccess($order, 'view');
        
        return $this->render('orders/show.html.twig', [
            'order' => [
                'id' => $order->getId(),

                // USER - use snapshot if user is deleted
                'userName' => $order->getUser()
                    ? $order->getUser()->getUsername()
                    : null,
                
                'userNameSnapshot' => $order->getUserNameSnapshot(),

                // CREATED BY
                'createdBy' => $order->getCreatedBy()
                    ? $order->getCreatedBy()->getUsername()
                    : 'N/A',

                // PRODUCT - use snapshot if product is deleted
                'productName' => $order->getProductId()
                    ? $order->getProductId()->getName()
                    : null,
                
                'productNameSnapshot' => $order->getProductNameSnapshot(),

                // SERVICE - use snapshot if service is deleted
                'serviceName' => $order->getServiceId()
                    ? $order->getServiceId()->getName()
                    : null,
                
                'serviceNameSnapshot' => $order->getServiceNameSnapshot(),

                // CATEGORY - use snapshot if category is deleted
                'categoryName' => $order->getCategoryId()
                    ? $order->getCategoryId()->getCategoryName()
                    : null,
                
                'categoryNameSnapshot' => $order->getCategoryNameSnapshot(),

                'price' => $order->getPrice(),
                'quantity' => $order->getQuantity(),
                'orderCreated' => $order->getOrderCreated(),
            ],
        ]);
    }

    #[Route('/delete/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Orders $order, 
        EntityManagerInterface $em,
        ActivityLogger $activityLogger
    ): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to delete this order
        $this->checkOrderAccess($order, 'delete');
        
        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            try {
                // Store order details for logging before deletion
                $orderId = $order->getId();
                $customerName = $order->getUser() ? $order->getUser()->getUsername() : 'Deleted User';
                $itemName = $order->getProductId() 
                    ? $order->getProductId()->getName() 
                    : ($order->getServiceId() ? $order->getServiceId()->getName() : 'No Item');
                $price = $order->getPrice();
                
                // If it's a product order, restore the quantity
                if ($order->getProductId()) {
                    $product = $order->getProductId();
                    $product->setQuantity($product->getQuantity() + $order->getQuantity());
                    $em->persist($product);
                }
                
                $em->remove($order);
                $em->flush();
                
                // Log the activity
                $activityLogger->log(
                    'Deleted Order',
                    'Order #' . $orderId . 
                    ' | Customer: ' . $customerName .
                    ' | Item: ' . $itemName .
                    ' | Price: ₱' . number_format($price, 2)
                );
                
                $this->addFlash('success', 'Order deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting order: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_orders_index');
    }

    /**
     * Check if the current user has access to the order
     */
    private function checkOrderAccess(Orders $order, string $action = 'view'): void
    {
        // Admin and Staff have full access to everything
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
            return;
        }

        // Check if user is authenticated
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        $currentUser = $this->getUser();
        
        // Staff can access orders they created or orders assigned to them
        // Note: This block is kept for backward compatibility but won't be reached due to above condition
        if ($this->isGranted('ROLE_STAFF')) {
            $orderCreator = $order->getCreatedBy();
            $orderUser = $order->getUser();
            
            // Staff can access orders they created
            if ($orderCreator && $currentUser === $orderCreator) {
                return;
            }
            
            // Staff can access orders assigned to customers (if they placed the order for a customer)
            if ($orderUser && $currentUser === $orderUser) {
                return;
            }
            
            throw new AccessDeniedException('You can only ' . $action . ' orders that you created or are assigned to you.');
        }

        // Regular users can only access their own orders
        if ($this->isGranted('ROLE_USER')) {
            $orderUser = $order->getUser();
            
            if ($orderUser && $currentUser === $orderUser) {
                return;
            }
            
            throw new AccessDeniedException('You can only ' . $action . ' your own orders.');
        }

        // For any other roles, deny access
        throw new AccessDeniedException('You do not have permission to ' . $action . ' orders.');
    }
}