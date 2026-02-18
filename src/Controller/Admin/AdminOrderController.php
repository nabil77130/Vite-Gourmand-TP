<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/order')]
#[IsGranted('ROLE_EMPLOYEE')]
class AdminOrderController extends AbstractController
{
    private const STATUSES = [
        'pending'          => 'En attente',
        'accepted'         => 'Acceptée',
        'preparing'        => 'En préparation',
        'delivering'       => 'En cours de livraison',
        'delivered'        => 'Livrée',
        'awaiting_return'  => 'En attente retour matériel',
        'completed'        => 'Terminée',
        'cancelled'        => 'Annulée',
    ];

    #[Route('', name: 'admin_order_index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        $qb = $orderRepository->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->addSelect('u')
            ->orderBy('o.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('o.status = :status')->setParameter('status', $status);
        }
        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $this->render('admin/order/index.html.twig', [
            'orders'   => $qb->getQuery()->getResult(),
            'statuses' => self::STATUSES,
            'current_status' => $status,
            'search'   => $search,
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order'    => $order,
            'statuses' => self::STATUSES,
        ]);
    }

    #[Route('/{id}/status', name: 'admin_order_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $newStatus = $request->request->get('status');
        if (array_key_exists($newStatus, self::STATUSES)) {
            $order->setStatus($newStatus);
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour : ' . self::STATUSES[$newStatus]);
        }
        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/cancel', name: 'admin_order_cancel', methods: ['POST'])]
    public function cancel(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $motif = $request->request->get('motif');
        $contact = $request->request->get('contact_mode');

        if ($motif && $contact) {
            $order->setStatus('cancelled');
            $order->setCancellationReason($motif . ' [Contact: ' . $contact . ']');
            $em->flush();
            $this->addFlash('success', 'Commande annulée.');
        }

        return $this->redirectToRoute('admin_order_index');
    }
}
