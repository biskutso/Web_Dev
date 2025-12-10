<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-black p-2']
            ])
            ->add('password', PasswordType::class, [
                'label' => $options['is_new'] ? 'Password' : 'Password (leave blank to keep current)',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-black p-2',
                    'placeholder' => $options['is_new'] ? 'Enter password' : 'Enter new password or leave blank',
                    'autocomplete' => 'new-password'
                ],
                'required' => $options['is_new'],
                'mapped' => false,
                'help' => $options['is_new'] ? null : 'Leave this field empty if you want to keep the current password',
                'help_attr' => ['class' => 'text-gray-500 text-sm mt-1'],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Staff' => 'ROLE_STAFF',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => false, // Single selection
                'expanded' => false, // Dropdown
                'attr' => ['class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-black p-2'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                ],
                'attr' => ['class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-black p-2']
            ]);

        // Data transformer to convert array to string and back
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesAsArray) {
                    // Transform array to string for form display
                    if (empty($rolesAsArray)) {
                        return 'ROLE_USER'; // Default role
                    }
                    
                    // Get the highest role (or first one)
                    // You can customize this logic based on your needs
                    if (in_array('ROLE_ADMIN', $rolesAsArray)) {
                        return 'ROLE_ADMIN';
                    } elseif (in_array('ROLE_STAFF', $rolesAsArray)) {
                        return 'ROLE_STAFF';
                    } else {
                        return 'ROLE_USER';
                    }
                },
                function ($rolesAsString) {
                    // Transform string to array for entity storage
                    return [$rolesAsString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => true,
        ]);
    }
}