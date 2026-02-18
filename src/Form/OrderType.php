<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $menu = $options['menu'];
        $minPeople = $menu?->getMinPeople() ?? 1;

        $builder
            ->add('adressePrestation', TextType::class, [
                'label' => 'Adresse de la prestation',
                'attr'  => ['placeholder' => 'Ex: 12 rue des Lilas, Bordeaux'],
                'constraints' => [new NotBlank(['message' => 'Veuillez indiquer l\'adresse.'])],
            ])
            ->add('eventDate', DateType::class, [
                'label'  => 'Date de la prestation',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('deliveryTime', TimeType::class, [
                'label'  => 'Heure souhaitée de livraison',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nombrePersonne', IntegerType::class, [
                'label' => 'Nombre de personnes (min. ' . $minPeople . ')',
                'data'  => $minPeople,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual([
                        'value'   => $minPeople,
                        'message' => 'Le nombre minimum de personnes pour ce menu est ' . $minPeople . '.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'menu'       => null,
        ]);
    }
}
