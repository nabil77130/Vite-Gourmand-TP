<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Politique de mot de passe de l'application, definie a un seul endroit.
 *
 * Elle est imposee a l'inscription, a la reinitialisation et a la creation
 * d'un compte employe par l'administrateur. Centraliser ces regles garantit
 * qu'un durcissement futur s'applique partout, sans oubli.
 *
 * Exigences : 10 caracteres minimum, dont au moins une majuscule, une
 * minuscule, un chiffre et un caractere special.
 */
final class PasswordPolicy
{
    /**
     * @return array<int, object> Les contraintes a appliquer au mot de passe en clair.
     */
    public static function constraints(): array
    {
        return [
            new NotBlank(
                message: 'Veuillez entrer un mot de passe',
            ),
            new Length(
                min: 10,
                minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                max: 4096,
            ),
            new Regex(
                pattern: '/[A-Z]/',
                message: 'Votre mot de passe doit contenir au moins une lettre majuscule',
            ),
            new Regex(
                pattern: '/[a-z]/',
                message: 'Votre mot de passe doit contenir au moins une lettre minuscule',
            ),
            new Regex(
                pattern: '/[0-9]/',
                message: 'Votre mot de passe doit contenir au moins un chiffre',
            ),
            new Regex(
                pattern: '/[\W_]/',
                message: 'Votre mot de passe doit contenir au moins un caractère spécial',
            ),
        ];
    }

    /**
     * Rappel affichable a l'utilisateur, pour qu'il sache quoi saisir
     * avant de se faire refuser le formulaire.
     */
    public static function helpText(): string
    {
        return '10 caractères minimum, dont au moins une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
}
