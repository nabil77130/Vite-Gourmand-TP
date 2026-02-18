<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Note (sur 5)',
                'choices' => [
                    '⭐⭐⭐⭐⭐ (Excellent)' => 5,
                    '⭐⭐⭐⭐ (Très bon)' => 4,
                    '⭐⭐⭐ (Bien)' => 3,
                    '⭐⭐ (Moyen)' => 2,
                    '⭐ (Mauvais)' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez donner une note.']),
                ],
                'attr' => ['class' => 'rating-stars'],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Qu\'avez-vous pensé de ce repas ?',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez laisser un commentaire.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
