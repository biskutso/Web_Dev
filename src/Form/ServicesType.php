<?php

namespace App\Form;

use App\Entity\Services;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Enum\ServicesType as EnumServicesType;
use Doctrine\ORM\EntityRepository;

class ServicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'category_name',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.type = :type')
                            ->setParameter('type', 'service');
                    },
                    'placeholder' => 'Select Service Category',
                ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Active' => EnumServicesType::Active,
                    'Inactive' => EnumServicesType::Inactive,
                ],
                'expanded' => false,
                'multiple' => false,
                'label' => 'Service Status',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}

