<?php

namespace App\Form;

use App\Entity\Allergen;
use App\Entity\Diet;
use App\Entity\Menu;
use App\Entity\Product;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Titre'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('conditions', TextareaType::class, [
                'label' => 'Conditions de ce menu',
                'help' => 'Délai de commande, précautions de stockage, matériel prêté… Affiché bien en évidence avant la commande.',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => "Ex. : ce menu doit être commandé au moins 7 jours avant la prestation. Les plats doivent être conservés au frais jusqu'au service.",
                ],
            ])
            ->add('price', NumberType::class, ['label' => 'Prix (€)', 'scale' => 2])
            ->add('minPeople', NumberType::class, ['label' => 'Nombre de personnes minimum', 'required' => false])
            ->add('stock', NumberType::class, ['label' => 'Stock disponible', 'required' => false])
            ->add('products', EntityType::class, [
                'class' => Product::class,
                'choice_label' => fn(Product $p) => $p->getName() . ' (' . $p->getCategory() . ')',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Plats composant ce menu',
                'required' => false,
            ])
            ->add('themes', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Thématiques du menu',
                'help' => 'Utilisé par les filtres de la carte.',
                'required' => false,
            ])
            ->add('diets', EntityType::class, [
                'class' => Diet::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Régimes alimentaires compatibles',
                'help' => 'Utilisé par les filtres de la carte.',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Menu::class]);
    }
}
