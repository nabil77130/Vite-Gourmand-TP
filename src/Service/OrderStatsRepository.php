<?php

namespace App\Service;

use App\Entity\Order;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Acces a la base NoSQL (MongoDB) dediee aux statistiques de commandes.
 *
 * L'enonce impose que les donnees du tableau de bord administrateur
 * proviennent d'une base non relationnelle. La base relationnelle reste la
 * source de verite metier (commandes, clients, menus) ; MongoDB recoit une
 * copie denormalisee de chaque commande, pensee pour la lecture statistique.
 *
 * Chaque document de la collection "order_stats" est autonome : il porte le
 * nom du menu et son prix au moment de la commande, ce qui evite toute
 * jointure a la lecture et garde l'historique juste meme si un menu est
 * renomme ou supprime plus tard.
 */
class OrderStatsRepository
{
    private const COLLECTION = 'order_stats';

    private ?Client $client = null;

    public function __construct(
        private readonly string $mongoUri,
        private readonly string $mongoDatabase,
    ) {
    }

    /**
     * La connexion n'est ouverte qu'au premier appel : les pages qui ne
     * consultent pas les statistiques ne paient pas le cout du reseau.
     */
    private function collection(): Collection
    {
        if ($this->client === null) {
            $this->client = new Client($this->mongoUri);
        }

        return $this->client->selectCollection($this->mongoDatabase, self::COLLECTION);
    }

    /**
     * Enregistre (ou met a jour) la photographie d'une commande.
     *
     * On utilise l'identifiant relationnel comme cle : rejouer l'operation
     * ne cree pas de doublon, ce qui rend la resynchronisation sans risque.
     */
    public function saveOrder(Order $order): void
    {
        $menu = null;
        foreach ($order->getOrderItems() as $item) {
            if (method_exists($item, 'getMenu') && $item->getMenu() !== null) {
                $menu = $item->getMenu();
                break;
            }
        }

        $document = [
            'orderId'        => $order->getId(),
            'menuId'         => $menu?->getId(),
            'menuName'       => $menu?->getName() ?? 'Menu non renseigné',
            'status'         => $order->getStatus(),
            'nombrePersonne' => $order->getNombrePersonne(),
            'totalPrice'     => (float) $order->getTotalPrice(),
            'prixLivraison'  => (float) ($order->getPrixLivraison() ?? 0),
            'createdAt'      => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
            'createdAtDay'   => $order->getCreatedAt()?->format('Y-m-d'),
        ];

        $this->collection()->updateOne(
            ['orderId' => $order->getId()],
            ['$set' => $document],
            ['upsert' => true]
        );
    }

    /**
     * Met a jour le seul statut, sans reecrire tout le document.
     */
    public function updateStatus(int $orderId, string $status): void
    {
        $this->collection()->updateOne(
            ['orderId' => $orderId],
            ['$set' => ['status' => $status]]
        );
    }

    public function removeOrder(int $orderId): void
    {
        $this->collection()->deleteOne(['orderId' => $orderId]);
    }

    /**
     * Vide entierement la collection de statistiques.
     *
     * Necessaire apres un rechargement des fixtures : les anciennes commandes
     * disparaissent de la base relationnelle mais leurs documents subsistent
     * dans MongoDB, ce qui fausserait les totaux du tableau de bord.
     *
     * @return int Nombre de documents supprimes
     */
    public function purgeAll(): int
    {
        return $this->collection()->deleteMany([])->getDeletedCount();
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu.
     *
     * C'est le coeur du tableau de bord : une seule agregation MongoDB
     * repond aux deux exigences de l'enonce (comparaison du nombre de
     * commandes par menu, et calcul du chiffre d'affaires par menu).
     *
     * @param array{menuId?: int|null, from?: string|null, to?: string|null} $filters
     *
     * @return array<int, array{menuId: int|null, menuName: string, orderCount: int, revenue: float, peopleTotal: int}>
     */
    public function statsByMenu(array $filters = []): array
    {
        $pipeline = [];

        $match = $this->buildMatch($filters);
        if ($match !== []) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$group' => [
            '_id'         => '$menuId',
            'menuName'    => ['$last' => '$menuName'],
            'orderCount'  => ['$sum' => 1],
            'revenue'     => ['$sum' => '$totalPrice'],
            'peopleTotal' => ['$sum' => '$nombrePersonne'],
        ]];
        $pipeline[] = ['$sort' => ['orderCount' => -1, 'revenue' => -1]];

        $rows = [];
        foreach ($this->collection()->aggregate($pipeline) as $doc) {
            $rows[] = [
                'menuId'      => $doc['_id'] !== null ? (int) $doc['_id'] : null,
                'menuName'    => (string) $doc['menuName'],
                'orderCount'  => (int) $doc['orderCount'],
                'revenue'     => round((float) $doc['revenue'], 2),
                'peopleTotal' => (int) ($doc['peopleTotal'] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Totaux generaux, pour les indicateurs en haut du tableau de bord.
     *
     * @param array{menuId?: int|null, from?: string|null, to?: string|null} $filters
     *
     * @return array{orderCount: int, revenue: float, averageBasket: float}
     */
    public function totals(array $filters = []): array
    {
        $pipeline = [];

        $match = $this->buildMatch($filters);
        if ($match !== []) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$group' => [
            '_id'        => null,
            'orderCount' => ['$sum' => 1],
            'revenue'    => ['$sum' => '$totalPrice'],
        ]];

        foreach ($this->collection()->aggregate($pipeline) as $doc) {
            $count = (int) $doc['orderCount'];
            $revenue = round((float) $doc['revenue'], 2);

            return [
                'orderCount'    => $count,
                'revenue'       => $revenue,
                'averageBasket' => $count > 0 ? round($revenue / $count, 2) : 0.0,
            ];
        }

        return ['orderCount' => 0, 'revenue' => 0.0, 'averageBasket' => 0.0];
    }

    /**
     * Chiffre d'affaires jour par jour, pour suivre l'activite sur une duree.
     *
     * @param array{menuId?: int|null, from?: string|null, to?: string|null} $filters
     *
     * @return array<int, array{day: string, orderCount: int, revenue: float}>
     */
    public function revenueByDay(array $filters = []): array
    {
        $pipeline = [];

        $match = $this->buildMatch($filters);
        if ($match !== []) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$group' => [
            '_id'        => '$createdAtDay',
            'orderCount' => ['$sum' => 1],
            'revenue'    => ['$sum' => '$totalPrice'],
        ]];
        $pipeline[] = ['$sort' => ['_id' => 1]];

        $rows = [];
        foreach ($this->collection()->aggregate($pipeline) as $doc) {
            if ($doc['_id'] === null) {
                continue;
            }
            $rows[] = [
                'day'        => (string) $doc['_id'],
                'orderCount' => (int) $doc['orderCount'],
                'revenue'    => round((float) $doc['revenue'], 2),
            ];
        }

        return $rows;
    }

    /**
     * Traduit les filtres du tableau de bord en critere MongoDB.
     *
     * Les dates sont comparees sur "createdAtDay" (AAAA-MM-JJ) : une
     * comparaison de chaines suffit dans ce format et evite les pieges de
     * fuseau horaire.
     *
     * @param array{menuId?: int|null, from?: string|null, to?: string|null} $filters
     *
     * @return array<string, mixed>
     */
    private function buildMatch(array $filters): array
    {
        $match = [];

        if (!empty($filters['menuId'])) {
            $match['menuId'] = (int) $filters['menuId'];
        }

        $range = [];
        if (!empty($filters['from'])) {
            $range['$gte'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $range['$lte'] = $filters['to'];
        }
        if ($range !== []) {
            $match['createdAtDay'] = $range;
        }

        return $match;
    }

    /**
     * Verifie que la base NoSQL repond, pour afficher un message clair
     * plutot qu'une page d'erreur si Atlas est injoignable.
     */
    public function isReachable(): bool
    {
        try {
            $this->collection()->countDocuments([], ['maxTimeMS' => 3000]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
