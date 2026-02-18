<?php

namespace App\Form;

use App\Entity\Allergen;
use App\Entity\Diet;
use App\Entity\Product;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du plat'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('price', NumberType::class, ['label' => 'Prix (€)', 'scale' => 2])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Entrée'  => 'starter',
                    'Plat'    => 'main',
                    'Dessert' => 'dessert',
                    'Boisson' => 'drink',
                ],
            ])
            ->add('isAvailable', CheckboxType::class, ['label' => 'Disponible', 'required' => false])
            ->add('imageName', TextType::class, ['label' => 'Nom de l\'image', 'required' => false])
            ->add('allergens', EntityType::class, [
                'class' => Allergen::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Allergènes',
                'required' => false,
            ])
            ->add('diets', EntityType::class, [
                'class' => Diet::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Régimes',
                'required' => false,
            ])
            ->add('themes', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Thèmes',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
