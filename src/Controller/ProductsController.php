<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use App\Repository\ActivityLogRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ActivityLogger;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/products')]
final class ProductsController extends AbstractController
{
    #[Route(name: 'app_products_index', methods: ['GET'])]
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('products/index.html.twig', [
            'products' => $productsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to create products (Admin or Staff)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw new AccessDeniedException('You do not have permission to create products.');
        }
        
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Automatically set the logged-in user as creator
            $user = $this->getUser(); // Get current user
            $product->setCreatedBy($user); // Set the user
            
            $entityManager->persist($product);
            $entityManager->flush();

            $activitylogger->log(
                'Created Product',
                'Product: ' . $product->getname() . ' (ID:' . $product->getId() . ')'
            );

            // Optional: Add success message
            $this->addFlash('success', 'Product created successfully!');
            
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        // Check access control
        $this->checkProductAccess($product, 'view');
        
        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkProductAccess($product, 'edit');
        
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $activitylogger->log(
                'Edited Product',
                'Product: ' . $product->getname() . ' ( ID:' . $product->getId() . ')'
            );

            $this->addFlash('success', 'Product updated successfully!');
            
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    public function delete(Request $request, Products $product, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkProductAccess($product, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $productId = $product->getId();
            $productName = $product->getName();
            
            $entityManager->remove($product);
            $entityManager->flush();
            
            $activitylogger->log(
                'Deleted Product',
                'Product: ' . $productName . ' (ID:' . $productId . ')'
            );

            $this->addFlash('success', 'Product deleted successfully!');
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Check if the current user has access to the product
     *
     * @param Products $product The product to check access for
     * @param string $action The action being performed (view, edit, delete)
     * @throws AccessDeniedException
     */
    private function checkProductAccess(Products $product, string $action = 'view'): void
    {
        // Admin has full access to everything
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Check if user is authenticated
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        $currentUser = $this->getUser();
        
        // Staff can only access their own products
        if ($this->isGranted('ROLE_STAFF')) {
            $productOwner = $product->getCreatedBy();
            
            // If current user or product owner is null, deny access
            if (!$currentUser || !$productOwner) {
                throw new AccessDeniedException('You can only ' . $action . ' products that you created.');
            }
            
            // Compare the user objects directly (recommended approach)
            // Symfony's security system compares user objects properly
            if ($currentUser === $productOwner) {
                return; // Staff can access their own products
            }
            
            // Alternative: Compare IDs if you prefer
            // if ($currentUser->getId() && $productOwner->getId() && 
            //     $currentUser->getId() === $productOwner->getId()) {
            //     return;
            // }
            
            // Staff trying to access someone else's product
            throw new AccessDeniedException('You can only ' . $action . ' products that you created.');
        }

        // For any other roles, deny access
        throw new AccessDeniedException('You do not have permission to ' . $action . ' products.');
    }
}