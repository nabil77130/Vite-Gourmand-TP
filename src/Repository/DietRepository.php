<?php

namespace App\Repository;

use App\Entity\Diet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Diet>
 */
class DietRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diet::class);
    }

    /**
     * Regimes alimentaires effectivement rattaches a au moins un menu.
     *
     * Meme logique que ThemeRepository::findUsedByMenus() : on ne propose dans
     * les filtres que des criteres susceptibles de donner un resultat.
     *
     * @return Diet[]
     */
    public function findUsedByMenus(): array
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.menus', 'm')
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
