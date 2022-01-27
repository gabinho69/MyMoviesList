<?php

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ],
            ])
            ->add('email', TextType::class,[
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ], 
            ])
            ->add('score',TextType::class,[
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ],
            ])
            ->add('votersNumber',TextType::class,[
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
