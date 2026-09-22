<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\OrderStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    /**
     * Delai laisse au client pour restituer le materiel prete, en jours ouvres.
     */
    private const EQUIPMENT_RETURN_WORKING_DAYS = 10;

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
    public function updateStatus(
        Request $request,
        Order $order,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        OrderStatsRepository $orderStats,
        LoggerInterface $logger
    ): Response {
        $newStatus = $request->request->get('status');

        if (array_key_exists($newStatus, self::STATUSES) && $newStatus !== $order->getStatus()) {
            /** @var \App\Entity\User|null $author */
            $author = $this->getUser();
            // addStatusHistory met a jour le statut courant ET trace le changement.
            $order->addStatusHistory($newStatus, $author);
            $em->flush();

            // Le tableau de bord lit MongoDB : on y reporte le nouveau statut.
            try {
                $orderStats->updateStatus($order->getId(), $newStatus);
            } catch (\Throwable $e) {
                $logger->error('Statut NoSQL non mis à jour pour la commande {id} : {message}', [
                    'id' => $order->getId(),
                    'message' => $e->getMessage(),
                ]);
            }

            $this->notifyCustomer($order, $newStatus, $mailer, $logger);

            $this->addFlash('success', 'Statut mis à jour : ' . self::STATUSES[$newStatus]);
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/cancel', name: 'admin_order_cancel', methods: ['POST'])]
    public function cancel(
        Request $request,
        Order $order,
        EntityManagerInterface $em,
        OrderStatsRepository $orderStats,
        LoggerInterface $logger
    ): Response {
        $motif = $request->request->get('motif');
        $contact = $request->request->get('contact_mode');

        if ($motif && $contact) {
            /** @var \App\Entity\User|null $author */
            $author = $this->getUser();
            $order->addStatusHistory('cancelled', $author);
            $order->setCancellationReason($motif . ' [Contact: ' . $contact . ']');
            $em->flush();

            try {
                $orderStats->updateStatus($order->getId(), 'cancelled');
            } catch (\Throwable $e) {
                $logger->error('Statut NoSQL non mis à jour pour la commande {id} : {message}', [
                    'id' => $order->getId(),
                    'message' => $e->getMessage(),
                ]);
            }

            $this->addFlash('success', 'Commande annulée.');
        }

        return $this->redirectToRoute('admin_order_index');
    }

    /**
     * Envoie au client le mail correspondant au nouveau statut, quand il y en a un.
     *
     * Deux statuts declenchent une notification, conformement a l'enonce :
     *   - "en attente du retour de materiel" : rappel du delai de 10 jours ouvres
     *     et des 600 EUR de frais prevus aux CGV ;
     *   - "terminee" : invitation a deposer un avis depuis l'espace client.
     *
     * Un echec d'envoi ne doit pas empecher le changement de statut : il est
     * journalise et signale a l'employe.
     */
    private function notifyCustomer(Order $order, string $status, MailerInterface $mailer, LoggerInterface $logger): void
    {
        if (!in_array($status, ['awaiting_return', 'completed'], true)) {
            return;
        }

        $user = $order->getUser();
        if ($user === null || !$user->getEmail()) {
            return;
        }

        $menuName = null;
        foreach ($order->getOrderItems() as $item) {
            if ($item->getMenu() !== null) {
                $menuName = $item->getMenu()->getName();
                break;
            }
        }

        try {
            if ($status === 'awaiting_return') {
                $html = $this->renderView('emails/equipment_return.html.twig', [
                    'firstName' => $user->getFirstName(),
                    'orderId'   => $order->getId(),
                    'menuName'  => $menuName,
                    'deadline'  => $this->addWorkingDays(new \DateTimeImmutable(), self::EQUIPMENT_RETURN_WORKING_DAYS)->format('d/m/Y'),
                    'cgvUrl'    => $this->generateUrl('app_cgv', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);
                $subject = 'Retour de matériel attendu sous 10 jours ouvrés — Vite & Gourmand';
            } else {
                $html = $this->renderView('emails/order_completed.html.twig', [
                    'firstName' => $user->getFirstName(),
                    'orderId'   => $order->getId(),
                    'menuName'  => $menuName,
                    'reviewUrl' => $this->generateUrl('order_review', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);
                $subject = 'Votre commande est terminée — donnez-nous votre avis';
            }

            $mailer->send(
                (new Email())
                    ->from('contact@vite-gourmand.fr')
                    ->to($user->getEmail())
                    ->subject($subject)
                    ->html($html)
            );
        } catch (\Throwable $e) {
            $logger->error('Notification client non envoyée pour la commande {id} : {message}', [
                'id' => $order->getId(),
                'message' => $e->getMessage(),
            ]);
            $this->addFlash('warning', "Le statut a été enregistré, mais l'email au client n'a pas pu être envoyé.");
        }
    }

    /**
     * Ajoute un nombre de jours ouvres a une date, en sautant samedis et dimanches.
     *
     * Les jours feries ne sont pas pris en compte : ce serait une precision
     * supplementaire a apporter avant une mise en production reelle.
     */
    private function addWorkingDays(\DateTimeImmutable $from, int $days): \DateTimeImmutable
    {
        $date = $from;
        $added = 0;

        while ($added < $days) {
            $date = $date->modify('+1 day');
            if (!in_array($date->format('N'), ['6', '7'], true)) {
                ++$added;
            }
        }

        return $date;
    }
}
