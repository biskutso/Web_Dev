<?php

namespace App\Controller;

use App\Entity\Customers;
use App\Form\CustomersType;
use App\Repository\CustomersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customers')]
final class CustomersController extends AbstractController
{
    #[Route(name: 'app_customers_index', methods: ['GET'])]
    public function index(CustomersRepository $customersRepository): Response
    {
        return $this->render('customers/index.html.twig', [
            'customers' => $customersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_customers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customers();
        $form = $this->createForm(CustomersType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('app_customers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customers/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customers_show', methods: ['GET'])]
    public function show(Customers $customer): Response
    {
        return $this->render('customers/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customers $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomersType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_customers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customers/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customers_delete', methods: ['POST'])]
    public function delete(Request $request, Customers $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customers_index', [], Response::HTTP_SEE_OTHER);
    }
}
