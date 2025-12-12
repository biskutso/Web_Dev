<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdersType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('selectedItem', HiddenType::class, [
                'mapped' => false,
                'label' => false,
            ])
            ->add('itemType', HiddenType::class, [
                'mapped' => false,
                'label' => false,
            ])
            ->add('price', HiddenType::class)
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantity',
                'data' => 1,
                'attr' => [
                    'readonly' => true,
                    'class' => 'hidden',
                ],
            ])
            ->add('categoryId', HiddenType::class, [
                'required' => false,
                'mapped' => false,
            ])
            // Add these for edit form - they'll be hidden but available
            ->add('productId', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a product',
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'hidden'],
                'mapped' => true,
            ])
            ->add('serviceId', EntityType::class, [
                'class' => Services::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a service',
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'hidden'],
                'mapped' => true,
            ]);

        // Add fields for edit mode
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $order = $event->getData();
            $form = $event->getForm();
            
            // Only add these fields if we're editing an existing order
            if ($order && $order->getId()) {
                $form->add('productId', EntityType::class, [
                    'class' => Products::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Select a product',
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'hidden'],
                    'mapped' => true,
                ]);
                
                $form->add('serviceId', EntityType::class, [
                    'class' => Services::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Select a service',
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'hidden'],
                    'mapped' => true,
                ]);
            }
        });

        // Handle form submission - REMOVE INVENTORY LOGIC FROM HERE
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $order = $event->getData();
            $form = $event->getForm();
            
            // Get selected item from hidden fields (card selection)
            $selectedItemId = $form->get('selectedItem')->getData();
            $itemType = $form->get('itemType')->getData();
            
            // If we have card selection data (from edit form)
            if ($selectedItemId && $itemType) {
                if ($itemType === 'product') {
                    $product = $this->entityManager->getRepository(Products::class)->find($selectedItemId);
                    if ($product) {
                        // DO NOT subtract quantity here - handle in controller
                        $order->setProductId($product);
                        $order->setServiceId(null); // Clear service if product is selected
                        
                        if ($product->getCategory()) {
                            $order->setCategoryId($product->getCategory());
                        }
                        
                        $order->setPrice($product->getPrice());
                    }
                } elseif ($itemType === 'service') {
                    $service = $this->entityManager->getRepository(Services::class)->find($selectedItemId);
                    if ($service) {
                        $order->setServiceId($service);
                        $order->setProductId(null); // Clear product if service is selected
                        
                        if ($service->getCategory()) {
                            $order->setCategoryId($service->getCategory());
                        }
                        
                        $order->setPrice($service->getPrice());
                    }
                }
            } else {
                // If no card selection, check the dropdown fields (fallback)
                $product = $order->getProductId();
                $service = $order->getServiceId();
                
                // Update order details - DO NOT handle inventory here
                if ($product) {
                    if ($product->getCategory()) {
                        $order->setCategoryId($product->getCategory());
                    }
                    $order->setPrice($product->getPrice());
                } elseif ($service) {
                    if ($service->getCategory()) {
                        $order->setCategoryId($service->getCategory());
                    }
                    $order->setPrice($service->getPrice());
                }
            }
            
            // Always set order quantity to 1
            $order->setQuantity(1);
            
            // Set order date if not set (only for new orders)
            if (!$order->getOrderCreated()) {
                $order->setOrderCreated(new \DateTimeImmutable());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}