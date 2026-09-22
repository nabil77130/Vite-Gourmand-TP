<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\DietRepository;
use App\Repository\MenuRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        MenuRepository $menuRepository,
        ThemeRepository $themeRepository,
        DietRepository $dietRepository
    ): Response {
        // Filtres transmis en GET : l'URL reste partageable et le bouton
        // "precedent" du navigateur fonctionne normalement.
        $filters = [
            'priceMin'  => $request->query->get('priceMin'),
            'priceMax'  => $request->query->get('priceMax'),
            'theme'     => $request->query->get('theme'),
            'diet'      => $request->query->get('diet'),
            'minPeople' => $request->query->get('minPeople'),
        ];

        $hasFilters = (bool) array_filter($filters, fn($v) => $v !== null && $v !== '');

        $menus = $hasFilters
            ? $menuRepository->findByFilters($filters)
            : $menuRepository->findAll();

        // La liste des plats suit les memes criteres de thematique et de regime,
        // pour rester coherente avec les menus affiches au-dessus.
        $products = ($filters['theme'] || $filters['diet'])
            ? $productRepository->findByFilters($filters)
            : $productRepository->findAll();

        // Group by category for display
        $groupedProducts = [];
        foreach ($products as $product) {
            $groupedProducts[$product->getCategory()][] = $product;
        }

        return $this->render('menu/index.html.twig', [
            'groupedProducts' => $groupedProducts,
            'menus'           => $menus,
            // Seuls les criteres rattaches a au moins un menu sont proposes,
            // pour ne jamais offrir un filtre qui ne renvoie aucun resultat.
            'themes'          => $themeRepository->findUsedByMenus(),
            'diets'           => $dietRepository->findUsedByMenus(),
            'filters'         => $filters,
            'hasFilters'      => $hasFilters,
            'priceRange'      => $menuRepository->findPriceRange(),
        ]);
    }

    #[Route('/menu/{id}', name: 'app_menu_show')]
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }
}
