<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\CategoriesType as EnumCategoriesType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category_name')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Product' => EnumCategoriesType::Product,
                    'Service' => EnumCategoriesType::Service,
                ],
                'choice_label' => fn ($choice) => ucfirst($choice->value),
                'expanded' => false, // true = radio buttons, false = dropdown
                'multiple' => false,
                'label' => 'Category Type',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
