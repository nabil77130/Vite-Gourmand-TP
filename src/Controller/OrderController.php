<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Form\OrderType;
use App\Repository\MenuRepository;
use App\Service\OrderPricer;
use App\Service\OrderStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
        EntityManagerInterface $em,
        MailerInterface $mailer,
        OrderStatsRepository $orderStats,
        OrderPricer $pricer,
        LoggerInterface $logger
    ): Response {
        $menu = $menuRepository->find($menuId);
        if (!$menu) {
            throw $this->createNotFoundException('Menu introuvable.');
        }

        // Controle cote serveur : masquer le bouton "Commander" ne suffit pas,
        // l'adresse de cette page peut etre saisie directement.
        if (!$menu->isAvailable()) {
            $this->addFlash('error', 'Ce menu est épuisé : il ne peut plus être commandé pour le moment.');

            return $this->redirectToRoute('app_menu_show', ['id' => $menu->getId()]);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTime());
        $order->setStatus('pending');
        $order->setTotalPrice($menu->getPrice() ?? 0);
        $order->setNombrePersonne($menu->getMinPeople() ?? 1);

        $form = $this->createForm(OrderType::class, $order, ['menu' => $menu]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Remise et frais de livraison : regles centralisees dans OrderPricer,
            // appliquees a l'identique a la creation et a la modification.
            $prix = $pricer->compute($menu, $order->getNombrePersonne(), $order->getAdressePrestation());
            $order->setTotalPrice($prix['total']);
            $order->setPrixLivraison($prix['deliveryPrice']);

            // Create OrderItem for this menu
            $item = new OrderItem();
            $item->setMenu($menu);
            $item->setQuantity(1);
            $item->setUnitPrice($menu->getPrice() ?? 0);
            $item->setOrderRef($order);
            $order->addOrderItem($item);

            // Premier etat de la commande, trace pour le suivi client.
            $order->addStatusHistory('pending', $user);

            // Une commande consomme une unite du stock du menu.
            $menu->decrementStock();

            $em->persist($order);
            $em->persist($item);
            $em->flush();

            // Copie de la commande vers la base NoSQL, qui alimente le tableau
            // de bord administrateur. Une indisponibilite de MongoDB ne doit
            // jamais faire echouer la commande du client : on journalise et on
            // continue, la commande pourra etre resynchronisee plus tard avec
            // la commande "app:sync-order-stats".
            try {
                $orderStats->saveOrder($order);
            } catch (\Throwable $e) {
                $logger->error('Statistiques NoSQL non enregistrées pour la commande {id} : {message}', [
                    'id'      => $order->getId(),
                    'message' => $e->getMessage(),
                ]);
            }

            // Envoi du mail de confirmation de commande
            $confirmationHtml = $this->renderView('emails/order_confirmation.html.twig', [
                'firstName' => $user->getFirstName(),
                'menuName' => $menu->getName(),
                'nombrePersonne' => $order->getNombrePersonne(),
                'adresse' => $order->getAdressePrestation(),
                'totalPrice' => number_format($order->getTotalPrice(), 2, ',', ' '),
                'ordersUrl' => $this->generateUrl('user_orders', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
            $confirmationEmail = (new Email())
                ->from('contact@vite-gourmand.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre commande — Vite & Gourmand')
                ->html($confirmationHtml);

            // La commande est deja enregistree : un echec d'envoi ne doit pas
            // afficher une erreur au client, qui croirait a tort que sa commande
            // a echoue et risquerait de la passer une seconde fois.
            try {
                $mailer->send($confirmationEmail);
            } catch (\Throwable $e) {
                $logger->error('Email de confirmation non envoyé pour la commande {id} : {message}', [
                    'id'      => $order->getId(),
                    'message' => $e->getMessage(),
                ]);
                $this->addFlash('warning', "Votre commande est bien enregistrée, mais l'email de confirmation n'a pas pu être envoyé. Vous la retrouvez dans « Mes commandes ».");
            }

            $this->addFlash('success', 'Votre commande a été passée avec succès !');
            return $this->redirectToRoute('user_orders');
        }

        return $this->render('order/create.html.twig', [
            'form'  => $form->createView(),
            'menu'  => $menu,
            'user'  => $user,
        ]);
    }

    /**
     * Modification d'une commande par son client.
     *
     * L'enonce l'autorise "tant qu'un employe n'a pas passe la commande en
     * accepte", et precise que "tout est modifiable, sauf le choix du menu".
     * Le menu est donc relu depuis la commande existante et n'est jamais
     * expose au formulaire : il ne peut pas etre change, meme en forgeant la
     * requete.
     */
    #[Route('/{id}/edit', name: 'order_edit', methods: ['GET', 'POST'])]
    public function edit(
        Order $order,
        Request $request,
        EntityManagerInterface $em,
        OrderPricer $pricer,
        OrderStatsRepository $orderStats,
        LoggerInterface $logger
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Cette commande ne vous appartient pas.');
        }

        if ($order->getStatus() !== 'pending') {
            $this->addFlash('error', "Cette commande a déjà été acceptée : elle n'est plus modifiable. Contactez-nous au 01 23 45 67 89.");

            return $this->redirectToRoute('user_orders');
        }

        $menu = $order->getMenu();

        if ($menu === null) {
            $this->addFlash('error', 'Le menu de cette commande est introuvable.');

            return $this->redirectToRoute('user_orders');
        }

        $form = $this->createForm(OrderType::class, $order, ['menu' => $menu]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prix = $pricer->compute($menu, $order->getNombrePersonne(), $order->getAdressePrestation());
            $order->setTotalPrice($prix['total']);
            $order->setPrixLivraison($prix['deliveryPrice']);

            $em->flush();

            // Le tableau de bord doit refleter le nouveau montant.
            try {
                $orderStats->saveOrder($order);
            } catch (\Throwable $e) {
                $logger->error('Statistiques NoSQL non mises a jour pour la commande {id} : {message}', [
                    'id'      => $order->getId(),
                    'message' => $e->getMessage(),
                ]);
            }

            $this->addFlash('success', 'Votre commande a été modifiée.');

            return $this->redirectToRoute('user_orders');
        }

        return $this->render('order/edit.html.twig', [
            'form'  => $form->createView(),
            'menu'  => $menu,
            'order' => $order,
            'user'  => $user,
        ]);
    }

    /**
     * Apercu du prix pendant la saisie de la commande.
     *
     * Le calcul est fait par le serveur, avec OrderPricer : la page affiche
     * donc exactement le montant qui sera facture, sans dupliquer les regles
     * de prix en JavaScript. C'est l'exigence de l'enonce : "une vue detaillee
     * du prix est visible avant validation".
     */
    #[Route('/price/{menuId}', name: 'order_price_preview', methods: ['GET'])]
    public function pricePreview(int $menuId, Request $request, MenuRepository $menuRepository, OrderPricer $pricer): JsonResponse
    {
        $menu = $menuRepository->find($menuId);
        if (!$menu) {
            return new JsonResponse(['error' => 'Menu introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $people = $request->query->getInt('people') ?: null;
        $address = (string) $request->query->get('address', '');

        return new JsonResponse($pricer->compute($menu, $people, $address));
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
            $order->addStatusHistory('cancelled', $user);
            // La commande annulee libere sa place dans le stock du menu.
            $order->getMenu()?->incrementStock();
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
