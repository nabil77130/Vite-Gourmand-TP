<?php

namespace App\Controller;


use App\Repository\MenuRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(MenuRepository $menuRepository, ReviewRepository $reviewRepository): Response
    {
        // Fetch featured menus
        $featuredMenus = $menuRepository->findBy([], ['price' => 'ASC'], 3);
        
        // Fetch latest approved reviews
        $reviews = $reviewRepository->findBy(['status' => 'approved'], ['id' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredMenus,
            'reviews' => $reviews,
        ]);
    }
}
