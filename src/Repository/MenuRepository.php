<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * Recherche les menus correspondant aux filtres de la carte.
     *
     * Chaque filtre est optionnel : une valeur nulle ou vide est ignoree,
     * ce qui permet de combiner librement les criteres.
     *
     * @param array{
     *     priceMin?: float|null,
     *     priceMax?: float|null,
     *     theme?: int|null,
     *     diet?: int|null,
     *     minPeople?: int|null
     * } $filters
     *
     * @return Menu[]
     */
    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.themes', 't')->addSelect('t')
            ->leftJoin('m.diets', 'd')->addSelect('d')
            ->orderBy('m.price', 'ASC');

        if (isset($filters['priceMin']) && $filters['priceMin'] !== '' && $filters['priceMin'] !== null) {
            $qb->andWhere('m.price >= :priceMin')
               ->setParameter('priceMin', (float) $filters['priceMin']);
        }

        if (isset($filters['priceMax']) && $filters['priceMax'] !== '' && $filters['priceMax'] !== null) {
            $qb->andWhere('m.price <= :priceMax')
               ->setParameter('priceMax', (float) $filters['priceMax']);
        }

        // Sous-requete : on ne veut pas que le leftJoin d'affichage restreigne le resultat,
        // sinon un menu filtre sur un theme perdrait l'affichage de ses autres themes.
        if (!empty($filters['theme'])) {
            $qb->andWhere('EXISTS (SELECT 1 FROM App\Entity\Menu m2 JOIN m2.themes t2 WHERE m2 = m AND t2.id = :theme)')
               ->setParameter('theme', (int) $filters['theme']);
        }

        if (!empty($filters['diet'])) {
            $qb->andWhere('EXISTS (SELECT 1 FROM App\Entity\Menu m3 JOIN m3.diets d3 WHERE m3 = m AND d3.id = :diet)')
               ->setParameter('diet', (int) $filters['diet']);
        }

        // "Je suis N personnes" : on garde les menus dont le minimum requis est atteignable.
        if (!empty($filters['minPeople'])) {
            $qb->andWhere('m.minPeople IS NULL OR m.minPeople <= :minPeople')
               ->setParameter('minPeople', (int) $filters['minPeople']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Prix le plus bas et le plus eleve de la carte, pour borner le filtre de prix.
     *
     * @return array{min: float, max: float}
     */
    public function findPriceRange(): array
    {
        $row = $this->createQueryBuilder('m')
            ->select('MIN(m.price) AS minPrice, MAX(m.price) AS maxPrice')
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'min' => (float) ($row['minPrice'] ?? 0),
            'max' => (float) ($row['maxPrice'] ?? 0),
        ];
    }
}
