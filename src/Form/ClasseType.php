<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Teacher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la classe',
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']
            ])
            ->add('level', TextType::class, [
                'label' => 'Niveau',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    'placeholder' => 'L1, L2, L3, M1, M2'
                ]
            ])
            ->add('field', TextType::class, [
                'label' => 'Filière',
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']
            ])
            ->add('maxCapacity', IntegerType::class, [
                'label' => 'Capacité maximale',
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']
            ])
            ->add('academicYear', TextType::class, [
                'label' => 'Année universitaire',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    'placeholder' => '2024-2025'
                ]
            ])
            ->add('supervisor', EntityType::class, [
                'class' => Teacher::class,
                'choice_label' => 'fullName',
                'label' => 'Responsable pédagogique',
                'required' => false,
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']
            ]);
    }

    // configureOptions est une méthode de configuration des options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // data_class est l'entité liée au formulaire
            'data_class' => Classe::class,
        ]);
    }
}

