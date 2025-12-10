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
        // Add this: Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Add this: Automatically set the logged-in user as creator
            $user = $this->getUser(); // Get current user
            $product->setCreatedBy($user); // Set the user
            
            $entityManager->persist($product);
            $entityManager->flush();

            $activitylogger->log(
                'Created Product',
                'Product: ' . $product->getname() . '(ID:' . $product->getId() . ')'
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
        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Optional: Add permission check
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Optional: Check if user owns the product or is admin
        // $user = $this->getUser();
        // if ($product->getCreatedBy() !== $user && !$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException('You can only edit your own products!');
        // }
        
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $activitylogger->log(
                'Edited Product',
                'Product: ' . $product->getname() . ' (ID:' . $product->getId() . ')'
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
        // Optional: Add permission check
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
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
}