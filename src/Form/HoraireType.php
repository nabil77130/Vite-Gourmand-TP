<?php

namespace App\Form;

use App\Entity\Horaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoraireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heureOuverture', TextType::class, [
                'label' => 'Heure d\'ouverture (ex: 08:00)',
                'required' => false,
                'attr' => ['placeholder' => '08:00'],
            ])
            ->add('heureFermeture', TextType::class, [
                'label' => 'Heure de fermeture (ex: 19:00)',
                'required' => false,
                'attr' => ['placeholder' => '19:00'],
            ])
            ->add('ferme', CheckboxType::class, [
                'label' => 'Fermé ce jour',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Horaire::class]);
    }
}
