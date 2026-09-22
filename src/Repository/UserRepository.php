<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Comptes employes, actifs comme desactives.
     *
     * Les roles sont stockes en JSON dans une seule colonne : on filtre donc
     * avec un LIKE, ce qui reste fiable ici car les libelles de roles ne se
     * chevauchent pas. Les administrateurs sont exclus : ils ne se gerent pas
     * depuis l'application.
     *
     * @return User[]
     */
    public function findEmployees(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :employee')
            ->andWhere('u.roles NOT LIKE :admin')
            ->setParameter('employee', '%ROLE_EMPLOYEE%')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
