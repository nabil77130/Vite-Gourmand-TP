<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Plats correspondant aux criteres de thematique et de regime.
     *
     * Seuls ces deux criteres s'appliquent aux plats : le prix et le nombre de
     * convives sont des notions propres aux menus (prix par personne, minimum
     * de convives) et n'ont pas de sens a l'echelle d'un plat.
     *
     * @param array{theme?: int|null, diet?: int|null} $filters
     *
     * @return Product[]
     */
    public function findByFilters(array $filters = []): array
    {
        // Meme ordre que findAll() : les plats restent groupes par categorie
        // dans l'ordre entree / plat / dessert / boisson a l'affichage.
        $qb = $this->createQueryBuilder('p')->orderBy('p.id', 'ASC');

        if (!empty($filters['theme'])) {
            $qb->andWhere('EXISTS (SELECT 1 FROM App\Entity\Product p2 JOIN p2.themes t2 WHERE p2 = p AND t2.id = :theme)')
               ->setParameter('theme', (int) $filters['theme']);
        }

        if (!empty($filters['diet'])) {
            $qb->andWhere('EXISTS (SELECT 1 FROM App\Entity\Product p3 JOIN p3.diets d3 WHERE p3 = p AND d3.id = :diet)')
               ->setParameter('diet', (int) $filters['diet']);
        }

        return $qb->getQuery()->getResult();
    }
}
