<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Premiere etape : le visiteur saisit son adresse email pour recevoir
 * le lien de reinitialisation.
 */
class ResetPasswordRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Votre adresse email',
            'attr' => [
                'placeholder' => 'vous@exemple.fr',
                'autocomplete' => 'email',
            ],
            'constraints' => [
                new NotBlank(message: 'Merci de saisir votre adresse email.'),
                new Email(message: 'Cette adresse email n\'est pas valide.'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Pas de data_class : ce formulaire n'est lie a aucune entite.
        $resolver->setDefaults([]);
    }
}
