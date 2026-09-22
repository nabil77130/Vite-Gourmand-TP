<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\PasswordPolicy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Creation d'un compte employe par l'administrateur.
 *
 * L'enonce precise que l'administrateur "doit fournir un email (qui sera
 * l'username) ainsi qu'un mot de passe" : c'est donc bien lui qui choisit le
 * mot de passe, et il le communiquera a l'employe de vive voix.
 *
 * Le role n'est volontairement pas un champ du formulaire : il est impose a
 * ROLE_EMPLOYEE par le controleur, pour qu'il soit impossible de creer un
 * compte administrateur depuis l'application.
 */
class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(message: 'Veuillez entrer le prénom.')],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(message: 'Veuillez entrer le nom.')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email (identifiant de connexion)',
                'constraints' => [new NotBlank(message: 'Veuillez entrer une adresse email.')],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'help' => PasswordPolicy::helpText() . " Il ne sera pas envoyé par email : communiquez-le à l'employé de vive voix.",
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => PasswordPolicy::constraints(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
