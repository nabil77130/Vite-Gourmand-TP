<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Form\OrderType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/create/{menuId}', name: 'order_create', methods: ['GET', 'POST'])]
    public function create(
        int $menuId,
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $em
    ): Response {
        $menu = $menuRepository->find($menuId);
        if (!$menu) {
            throw $this->createNotFoundException('Menu introuvable.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTime());
        $order->setStatus('pending');
        $order->setTotalPrice($menu->getPrice() ?? 0);

        $form = $this->createForm(OrderType::class, $order, ['menu' => $menu]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nombrePersonne = $order->getNombrePersonne() ?? $menu->getMinPeople() ?? 1;
            $basePrice = $menu->getPrice() ?? 0;

            // Discount: 10% if nombrePersonne >= minPeople + 5
            $minPeople = $menu->getMinPeople() ?? 1;
            $discount = 0;
            if ($nombrePersonne >= $minPeople + 5) {
                $discount = $basePrice * 0.10;
            }

            // Delivery price: 5€ base + 0.59€/km if not Bordeaux
            $adresse = strtolower($order->getAdressePrestation() ?? '');
            $prixLivraison = 0;
            if (!str_contains($adresse, 'bordeaux')) {
                $prixLivraison = 5.0; // base, km calculation would need geocoding
            }

            $total = ($basePrice - $discount) + $prixLivraison;
            $order->setTotalPrice(round($total, 2));
            $order->setPrixLivraison($prixLivraison);

            // Create OrderItem for this menu
            $item = new OrderItem();
            $item->setMenu($menu);
            $item->setQuantity(1);
            $item->setUnitPrice($menu->getPrice() ?? 0);
            $item->setOrderRef($order);
            $order->addOrderItem($item);

            $em->persist($order);
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Votre commande a été passée avec succès !');
            return $this->redirectToRoute('user_orders');
        }

        return $this->render('order/create.html.twig', [
            'form'  => $form->createView(),
            'menu'  => $menu,
            'user'  => $user,
        ]);
    }

    #[Route('/{id}/cancel', name: 'order_cancel', methods: ['POST'])]
    public function cancel(Order $order, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }
        if ($order->getStatus() === 'pending') {
            $order->setStatus('cancelled');
            $em->flush();
            $this->addFlash('success', 'Commande annulée.');
        } else {
            $this->addFlash('error', 'Cette commande ne peut plus être annulée.');
        }
        return $this->redirectToRoute('user_orders');
    }

    #[Route('/{id}/review', name: 'order_review', methods: ['GET', 'POST'])]
    public function review(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Security checks
        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas noter cette commande.');
        }
        if ($order->getStatus() !== 'completed') {
            $this->addFlash('error', 'Vous ne pouvez noter que les commandes terminées.');
            return $this->redirectToRoute('user_orders');
        }
        if ($order->getReview()) {
            $this->addFlash('warning', 'Vous avez déjà noté cette commande.');
            return $this->redirectToRoute('user_orders');
        }

        $review = new Review();
        $review->setOrderRef($order);
        
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setStatus('pending'); // Reviews must be approved
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre avis ! Il sera publié après validation.');
            return $this->redirectToRoute('user_orders');
        }

        return $this->render('order/review.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
        ]);
    }
}
