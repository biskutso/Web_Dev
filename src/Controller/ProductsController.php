<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw new AccessDeniedException('You do not have permission to create products.');
        }
        
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $product->setCreatedBy($user);
            
            $entityManager->persist($product);
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Created Product',
                'Product: ' . $product->getName() . ' (ID:' . $product->getId() . ')'
            );

            $this->addFlash('success', 'Product created successfully!');
            
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        $this->checkProductAccess($product, 'view');
        
        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkProductAccess($product, 'edit');
        
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Edited Product',
                'Product: ' . $product->getName() . ' (ID:' . $product->getId() . ')'
            );

            $this->addFlash('success', 'Product updated successfully!');
            
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    public function delete(Request $request, Products $product, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkProductAccess($product, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $productId = $product->getId();
            $productName = $product->getName();
            
            $entityManager->remove($product);
            $entityManager->flush();
            
            // Use standardized action names
            $activitylogger->log(
                'Deleted Product',
                'Product: ' . $productName . ' (ID:' . $productId . ')'
            );

            $this->addFlash('success', 'Product deleted successfully!');
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkProductAccess(Products $product, string $action = 'view'): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        $currentUser = $this->getUser();
        
        if ($this->isGranted('ROLE_STAFF')) {
            $productOwner = $product->getCreatedBy();
            
            if (!$currentUser || !$productOwner) {
                throw new AccessDeniedException('You can only ' . $action . ' products that you created.');
            }
            
            if ($currentUser === $productOwner) {
                return;
            }
            
            throw new AccessDeniedException('You can only ' . $action . ' products that you created.');
        }

        throw new AccessDeniedException('You do not have permission to ' . $action . ' products.');
    }
}