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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Menu::class]);
    }
}
