<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Thematiques effectivement rattachees a au moins un menu.
     *
     * Les filtres de la carte sont construits a partir de cette liste : une
     * thematique sans menu n'est pas proposee, ce qui evite un filtre qui ne
     * renvoie jamais de resultat. Elle reste disponible cote administration
     * et reapparait d'elle-meme des qu'un menu l'utilise.
     *
     * @return Theme[]
     */
    public function findUsedByMenus(): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.menus', 'm')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
