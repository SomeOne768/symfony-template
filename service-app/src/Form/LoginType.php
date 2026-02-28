<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('email', EmailType::class, [
                'label' => 'Email',

                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],

                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            ->add('plainPassword', TextType::class, [
                'label' => 'Password',

                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],

                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-4',
                ],

                // 'label' => 'xxx' // Pour FR à gérer
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
