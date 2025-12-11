<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/categories')]
final class CategoriesController extends AbstractController
{
    #[Route(name: 'app_categories_index', methods: ['GET'])]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('categories/index.html.twig', [
            'categories' => $categoriesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to create categories (Admin or Staff)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw new AccessDeniedException('You do not have permission to create categories.');
        }
        
        $category = new Categories();
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Automatically set the logged-in user as creator
            $user = $this->getUser(); // Get current user
            $category->setCreatedBy($user); // Set the user
            
            $entityManager->persist($category);
            $entityManager->flush();

            $activitylogger->log(
                'Created Category',
                'Category: ' . $category->getCategoryName() . ' (ID:' . $category->getId() . ')'
            );

            // Optional: Add success message
            $this->addFlash('success', 'Category created successfully!');
            
            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_categories_show', methods: ['GET'])]
    public function show(Categories $category): Response
    {
        // Check access control
        $this->checkCategoryAccess($category, 'view');
        
        return $this->render('categories/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categories $category, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkCategoryAccess($category, 'edit');
        
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

             $activitylogger->log(
                'Edited Category',
                'Category: ' . $category->getCategoryName() . ' (ID:' . $category->getId() . ')'
            );

            $this->addFlash('success', 'Category updated successfully!');
            
            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_categories_delete', methods: ['POST'])]
    public function delete(Request $request, Categories $category, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkCategoryAccess($category, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $categoryId = $category->getId();
            $categoryName = $category->getCategoryName();
            
            $entityManager->remove($category);
            $entityManager->flush();
            
            $activitylogger->log(
                'Deleted Category',
                'Category: ' . $categoryName . ' (ID:' . $categoryId . ')'
            );

            $this->addFlash('success', 'Category deleted successfully!');
        }

        return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Check if the current user has access to the category
     *
     * @param Categories $category The category to check access for
     * @param string $action The action being performed (view, edit, delete)
     * @throws AccessDeniedException
     */
    private function checkCategoryAccess(Categories $category, string $action = 'view'): void
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
        
        // Staff can only access their own categories
        if ($this->isGranted('ROLE_STAFF')) {
            $categoryOwner = $category->getCreatedBy();
            
            // If current user or category owner is null, deny access
            if (!$currentUser || !$categoryOwner) {
                throw new AccessDeniedException('You can only ' . $action . ' categories that you created.');
            }
            
            // Compare the user objects directly (recommended approach)
            if ($currentUser === $categoryOwner) {
                return; // Staff can access their own categories
            }
            
            // Staff trying to access someone else's category
            throw new AccessDeniedException('You can only ' . $action . ' categories that you created.');
        }

        // For any other roles, deny access
        throw new AccessDeniedException('You do not have permission to ' . $action . ' categories.');
    }
}