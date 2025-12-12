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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw new AccessDeniedException('You do not have permission to create services.');
        }
        
        $service = new Services();
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $service->setCreatedBy($user);
            
            $entityManager->persist($service);
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Created Service',
                'Service: ' . $service->getName() . ' (ID:' . $service->getId() . ')'
            );

            $this->addFlash('success', 'Service created successfully!');
            
            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_services_show', methods: ['GET'])]
    public function show(Services $service): Response
    {
        $this->checkServiceAccess($service, 'view');
        
        return $this->render('services/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_services_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Services $service, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkServiceAccess($service, 'edit');
        
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Edited Service',
                'Service: ' . $service->getName() . ' (ID:' . $service->getId() . ')'
            );

            $this->addFlash('success', 'Service updated successfully!');
            
            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_services_delete', methods: ['POST'])]
    public function delete(Request $request, Services $service, EntityManagerInterface $entityManager, ActivityLogger $activitylogger): Response
    {
        $this->checkServiceAccess($service, 'delete');
        
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $serviceId = $service->getId();
            $serviceName = $service->getName();
           
            $entityManager->remove($service);
            $entityManager->flush();

            // Use standardized action names
            $activitylogger->log(
                'Deleted Service',
                'Service: ' . $serviceName . ' (ID:' . $serviceId . ')'
            );
            
            $this->addFlash('success', 'Service deleted successfully!');
        }

        return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkServiceAccess(Services $service, string $action = 'view'): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        $currentUser = $this->getUser();
        
        if ($this->isGranted('ROLE_STAFF')) {
            $serviceOwner = $service->getCreatedBy();
            
            if (!$currentUser || !$serviceOwner) {
                throw new AccessDeniedException('You can only ' . $action . ' services that you created.');
            }
            
            if ($currentUser === $serviceOwner) {
                return;
            }
            
            throw new AccessDeniedException('You can only ' . $action . ' services that you created.');
        }

        throw new AccessDeniedException('You do not have permission to ' . $action . ' services.');
    }
}