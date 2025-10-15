<?php

namespace App\Form;

use App\Entity\Customers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CustomersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_name')
            ->add('customer_email', EmailType::class, [ 
                'label' => 'Email Address',
                'required' => true,
                'attr' => [
                    'placeholder' => 'example@gmail.com',
                    'class' => 'form-control'
                ]
            ])
            ->add('customer_password')
            ->add('customer_address')
            ->add('customer_phonenumber')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customers::class,
        ]);
    }
}

