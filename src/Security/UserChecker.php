<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Verification effectuee a chaque tentative de connexion.
 *
 * L'enonce demande qu'un compte employe puisse etre rendu inutilisable, par
 * exemple lors d'un depart de l'entreprise. Plutot que de supprimer le compte
 * — ce qui ferait perdre l'historique des actions —, on le desactive : le
 * controle ci-dessous refuse alors l'authentification.
 *
 * Le controle est place avant la verification du mot de passe : un compte
 * desactive est refuse meme si les identifiants sont corrects.
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Ce compte a été désactivé. Merci de contacter l\'administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucun controle supplementaire apres authentification.
    }
}
