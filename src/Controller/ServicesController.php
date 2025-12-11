<?php

namespace App\Controller;

use App\Entity\Services;
use App\Form\ServicesType;
use App\Repository\ServicesRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/services')]
final class ServicesController extends AbstractController
{
    #[Route(name: 'app_services_index', methods: ['GET'])]
    public function index(ServicesRepository $servicesRepository): Response
    {
        return $this->render('services/index.html.twig', [
            'services' => $servicesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_services_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check if user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Check if user has permission to create services (Admin or Staff)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw new AccessDeniedException('You do not have permission to create services.');
        }
        
        $service = new Services();
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Automatically set the logged-in user as creator
            $user = $this->getUser(); // Get current user
            $service->setCreatedBy($user); // Set the user
            
            $entityManager->persist($service);
            $entityManager->flush();

            $activitylogger->log(
                'Created Service',
                'Service: ' . $service->getname() . ' (ID:' . $service->getId() . ')'
            );

            // Optional: Add success message
            $this->addFlash('success', 'Service created successfully!');
            
            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_services_show', methods: ['GET'])]
    public function show(Services $service): Response
    {
        // Check access control
        $this->checkServiceAccess($service, 'view');
        
        return $this->render('services/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_services_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Services $service, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkServiceAccess($service, 'edit');
        
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $activitylogger->log(
                'Edited Service',
                'Service: ' . $service->getname() . ' (ID:' . $service->getId() . ')'
            );

            $this->addFlash('success', 'Service updated successfully!');
            
            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(), // Use createView() here
        ]);
    }

    #[Route('/{id}', name: 'app_services_delete', methods: ['POST'])]
    public function delete(Request $request, Services $service, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        // Check access control
        $this->checkServiceAccess($service, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $serviceId = $service->getId();
            $serviceName = $service->getName();
           
            $entityManager->remove($service);
            $entityManager->flush();

            $activitylogger->log(
                'Deleted Service',
                'Service: ' . $serviceName . ' (ID:' . $serviceId . ')'
            );
            
            $this->addFlash('success', 'Service deleted successfully!');
        }

        return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Check if the current user has access to the service
     *
     * @param Services $service The service to check access for
     * @param string $action The action being performed (view, edit, delete)
     * @throws AccessDeniedException
     */
    private function checkServiceAccess(Services $service, string $action = 'view'): void
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
        
        // Staff can only access their own services
        if ($this->isGranted('ROLE_STAFF')) {
            $serviceOwner = $service->getCreatedBy();
            
            // If current user or service owner is null, deny access
            if (!$currentUser || !$serviceOwner) {
                throw new AccessDeniedException('You can only ' . $action . ' services that you created.');
            }
            
            // Compare the user objects directly (recommended approach)
            if ($currentUser === $serviceOwner) {
                return; // Staff can access their own services
            }
            
            // Staff trying to access someone else's service
            throw new AccessDeniedException('You can only ' . $action . ' services that you created.');
        }

        // For any other roles, deny access
        throw new AccessDeniedException('You do not have permission to ' . $action . ' services.');
    }
}