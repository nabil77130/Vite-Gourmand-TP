<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(ProductRepository $productRepository, MenuRepository $menuRepository): Response
    {
        $products = $productRepository->findAll();
        $menus = $menuRepository->findAll();

        // Group by category for display
        $groupedProducts = [];
        foreach ($products as $product) {
            $groupedProducts[$product->getCategory()][] = $product;
        }

        return $this->render('menu/index.html.twig', [
            'groupedProducts' => $groupedProducts,
            'menus' => $menus,
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
