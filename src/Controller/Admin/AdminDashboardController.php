<?php

namespace App\Controller\Admin;

use App\Repository\MenuRepository;
use App\Service\OrderStatsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord de l'administrateur.
 *
 * Repond a deux exigences de l'enonce :
 *   - comparer le nombre de commandes par menu au moyen d'un graphique,
 *     les donnees provenant d'une base non relationnelle ;
 *   - calculer le chiffre d'affaires par menu, avec un filtre par menu
 *     et un filtre sur une duree.
 *
 * Reserve a ROLE_ADMIN : un employe n'y a pas acces.
 */
#[Route('/admin/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        Request $request,
        OrderStatsRepository $orderStats,
        MenuRepository $menuRepository
    ): Response {
        $filters = [
            'menuId' => $request->query->get('menuId'),
            'from'   => $request->query->get('from'),
            'to'     => $request->query->get('to'),
        ];

        // Si Atlas est injoignable, on affiche la page avec un avertissement
        // plutot qu'une erreur 500 : le reste de l'administration reste utilisable.
        if (!$orderStats->isReachable()) {
            return $this->render('admin/dashboard/index.html.twig', [
                'unreachable' => true,
                'filters'     => $filters,
                'menus'       => $menuRepository->findBy([], ['name' => 'ASC']),
                'statsByMenu' => [],
                'totals'      => ['orderCount' => 0, 'revenue' => 0.0, 'averageBasket' => 0.0],
                'revenueByDay' => [],
            ]);
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'unreachable'  => false,
            'filters'      => $filters,
            'menus'        => $menuRepository->findBy([], ['name' => 'ASC']),
            'statsByMenu'  => $orderStats->statsByMenu($filters),
            'totals'       => $orderStats->totals($filters),
            'revenueByDay' => $orderStats->revenueByDay($filters),
        ]);
    }
}
