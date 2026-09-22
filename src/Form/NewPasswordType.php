<?php

namespace App\Form;

use App\Validator\PasswordPolicy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Seconde etape : l'utilisateur, arrive par le lien recu par mail,
 * choisit son nouveau mot de passe et le confirme.
 */
class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
            'first_options' => [
                'label' => 'Nouveau mot de passe',
                'help' => PasswordPolicy::helpText(),
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => '••••••••'],
            ],
            'second_options' => [
                'label' => 'Confirmez le nouveau mot de passe',
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => '••••••••'],
            ],
            'mapped' => false,
            'constraints' => PasswordPolicy::constraints(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
