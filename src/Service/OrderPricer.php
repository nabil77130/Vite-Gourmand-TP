<?php

namespace App\Service;

use App\Entity\Menu;

/**
 * Calcul du prix d'une commande.
 *
 * Regroupe ici les trois regles de gestion de l'enonce, pour qu'elles soient
 * appliquees a l'identique a la creation et a la modification d'une commande :
 *
 *   - le prix de base est celui du menu, pour son nombre minimum de convives ;
 *   - une reduction de 10 % s'applique des que la commande compte au moins
 *     5 convives de plus que ce minimum ;
 *   - une livraison hors de Bordeaux est facturee 5 EUR, majores de
 *     0,59 EUR par kilometre parcouru.
 */
class OrderPricer
{
    /**
     * Nombre de convives supplementaires ouvrant droit a la reduction.
     */
    private const DISCOUNT_THRESHOLD = 5;

    private const DISCOUNT_RATE = 0.10;

    private const DELIVERY_BASE = 5.0;

    private const DELIVERY_PER_KM = 0.59;

    /**
     * Distances indicatives depuis Bordeaux, en kilometres.
     *
     * Une veritable application interrogerait un service de geocodage. Faute
     * d'un tel service ici, on s'appuie sur une table des communes de la
     * metropole, avec une distance par defaut au-dela. Le choix est assume et
     * documente : la regle des 0,59 EUR/km est bien appliquee, seule la mesure
     * de la distance est approchee.
     */
    private const DISTANCES_KM = [
        'merignac'      => 8,
        'mérignac'      => 8,
        'pessac'        => 8,
        'talence'       => 5,
        'begles'        => 6,
        'bègles'        => 6,
        'villenave'     => 10,
        'gradignan'     => 10,
        'cenon'         => 5,
        'floirac'       => 6,
        'lormont'       => 7,
        'bruges'        => 6,
        'eysines'       => 9,
        'le bouscat'    => 5,
        'blanquefort'   => 12,
        'saint-medard'  => 15,
        'saint-médard'  => 15,
        'ambares'       => 16,
        'ambarès'       => 16,
        'arcachon'      => 60,
        'libourne'      => 35,
        'langon'        => 50,
    ];

    /**
     * Distance retenue lorsque la commune n'est pas reconnue.
     */
    private const DEFAULT_DISTANCE_KM = 20;

    /**
     * Detaille le prix d'une commande.
     *
     * @return array{basePrice: float, discount: float, deliveryPrice: float, distanceKm: int, total: float}
     */
    public function compute(Menu $menu, ?int $people, ?string $address): array
    {
        $basePrice = (float) ($menu->getPrice() ?? 0);
        $minPeople = $menu->getMinPeople() ?? 1;
        $people = $people ?? $minPeople;

        $discount = $people >= $minPeople + self::DISCOUNT_THRESHOLD
            ? $basePrice * self::DISCOUNT_RATE
            : 0.0;

        [$deliveryPrice, $distanceKm] = $this->computeDelivery($address);

        return [
            'basePrice'     => round($basePrice, 2),
            'discount'      => round($discount, 2),
            'deliveryPrice' => round($deliveryPrice, 2),
            'distanceKm'    => $distanceKm,
            'total'         => round($basePrice - $discount + $deliveryPrice, 2),
        ];
    }

    /**
     * Frais de livraison et distance retenue.
     *
     * Une prestation dans Bordeaux meme n'entraine aucun frais.
     *
     * @return array{0: float, 1: int}
     */
    private function computeDelivery(?string $address): array
    {
        $normalized = mb_strtolower(trim((string) $address));

        if ($normalized === '' || str_contains($normalized, 'bordeaux')) {
            return [0.0, 0];
        }

        $distance = self::DEFAULT_DISTANCE_KM;
        foreach (self::DISTANCES_KM as $city => $km) {
            if (str_contains($normalized, $city)) {
                $distance = $km;
                break;
            }
        }

        return [self::DELIVERY_BASE + $distance * self::DELIVERY_PER_KM, $distance];
    }
}
