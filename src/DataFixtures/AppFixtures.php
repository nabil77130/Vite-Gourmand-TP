<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\Diet;
use App\Entity\Horaire;
use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 0. Create Horaires
        $horairesData = [
            ['lundi', '08:00', '19:00', false],
            ['mardi', '08:00', '19:00', false],
            ['mercredi', '08:00', '19:00', false],
            ['jeudi', '08:00', '19:00', false],
            ['vendredi', '08:00', '19:00', false],
            ['samedi', '09:00', '17:00', false],
            ['dimanche', null, null, true],
        ];
        foreach ($horairesData as [$jour, $ouv, $ferm, $ferme]) {
            $h = new Horaire();
            $h->setJour($jour);
            $h->setHeureOuverture($ouv);
            $h->setHeureFermeture($ferm);
            $h->setFerme($ferme);
            $manager->persist($h);
        }

        // 1. Create Users
        $admin = new User();
        $admin->setEmail('admin@vite-gourmand.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'mdp123456789'));
        $admin->setFirstName('Julie');
        $admin->setLastName('Manager');
        $admin->setPhone('0600000000');
        $manager->persist($admin);

        $employee = new User();
        $employee->setEmail('employee@vite-gourmand.com');
        $employee->setRoles(['ROLE_EMPLOYEE']);
        $employee->setPassword($this->userPasswordHasher->hashPassword($employee, 'mdp123456789'));
        $employee->setFirstName('Marc');
        $employee->setLastName('Employé');
        $employee->setPhone('0611111111');
        $manager->persist($employee);

        $customer = new User();
        $customer->setEmail('user@vite-gourmand.com');
        $customer->setRoles(['ROLE_USER']);
        $customer->setPassword($this->userPasswordHasher->hashPassword($customer, 'mdp123456789'));
        $customer->setFirstName('Jean');
        $customer->setLastName('Dupont');
        $customer->setAddress('123 Rue de Paris');
        $customer->setCity('Paris');
        $customer->setZipCode('75001');
        $customer->setPhone('0612345678');
        $manager->persist($customer);

        // 2. Create Reference Data (Diets, Allergens, Themes)
        $diets = [];
        foreach (['Végétarien', 'Vegan', 'Sans Gluten', 'Halal'] as $name) {
            $diet = new Diet();
            $diet->setName($name);
            $manager->persist($diet);
            $diets[$name] = $diet;
        }

        $allergens = [];
        foreach (['Arachides', 'Produits Laitiers', 'Gluten', 'Soja'] as $name) {
            $allergen = new Allergen();
            $allergen->setName($name);
            $manager->persist($allergen);
            $allergens[$name] = $allergen;
        }

        $themes = [];
        foreach (['Italien', 'Asiatique', 'Français', 'Mexicain'] as $name) {
            $theme = new Theme();
            $theme->setName($name);
            $manager->persist($theme);
            $themes[$name] = $theme;
        }

        // 3. Create Products
        $products = [];
        // [Name, Description, Price, Category, ImageName, [Diets], [Allergens], [Themes]]
        $productData = [
            ['Bruschetta', 'Tartines de tomates et basilic', 6.50, 'starter', 'bruschetta.jpg', ['Végétarien'], [], ['Italien']],
            ['Rouleaux de Printemps', 'Rouleaux vietnamiens croustillants', 5.00, 'starter', 'spring_rolls.jpg', [], ['Gluten'], ['Asiatique']],
            ['Pizza Margherita', 'Tomate, mozzarella, basilic', 12.00, 'main', 'pizza_margherita.jpg', ['Végétarien'], ['Gluten', 'Produits Laitiers'], ['Italien']],
            ['Pad Thai', 'Nouilles de riz au tofu', 14.50, 'main', 'pad_thai.jpg', ['Sans Gluten'], ['Arachides', 'Soja'], ['Asiatique']],
            ['Tiramisu', 'Dessert au café', 7.00, 'dessert', 'tiramisu.jpg', ['Végétarien'], ['Produits Laitiers', 'Gluten'], ['Italien']],
            ['Salade de Fruits', 'Fruits de saison frais', 5.00, 'dessert', 'fruit_salad.webp', ['Vegan', 'Sans Gluten'], [], ['Français']],
            ['Coca Cola', 'Canette 33cl', 2.50, 'drink', 'coca_cola.jpg', [], [], []],
            ['Evian', 'Bouteille 50cl', 2.00, 'drink', 'evian.webp', [], [], []],
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product->setName($data[0]);
            $product->setDescription($data[1]);
            $product->setPrice($data[2]);
            $product->setCategory($data[3]);
            $product->setImageName($data[4]);
            $product->setAvailable(true);

            foreach ($data[5] as $dietName)
                $product->addDiet($diets[$dietName]);
            foreach ($data[6] as $allergenName)
                $product->addAllergen($allergens[$allergenName]);
            foreach ($data[7] as $themeName)
                $product->addTheme($themes[$themeName]);

            $manager->persist($product);
            $products[] = $product;
        }

        // 4. Create Menus
        $menu1 = new Menu();
        $menu1->setName('Festin Italien');
        $menu1->setDescription('Une expérience complète italienne avec entrée, plat et dessert.');
        $menu1->setPrice(22.00);
        $menu1->setMinPeople(10);
        $menu1->setStock(15);
        $menu1->setImageName('festin_italien.jpg');
        $menu1->setConditions("Ce menu doit être commandé au moins 7 jours avant la prestation.\nLes plats sont livrés dans des plats de service prêtés par l'entreprise : ils devront être restitués sous 10 jours ouvrés après la prestation.\nConserver au frais jusqu'au service.");
        $menu1->addProduct($products[0]); // Bruschetta
        $menu1->addProduct($products[2]); // Pizza
        $menu1->addProduct($products[4]); // Tiramisu
        // Thematique et regime : utilises par les filtres de la carte
        $menu1->addTheme($themes['Italien']);
        $manager->persist($menu1);

        $menu2 = new Menu();
        $menu2->setName('Menu Végétarien');
        $menu2->setDescription('Un menu 100% végétarien, frais et savoureux.');
        $menu2->setPrice(18.00);
        $menu2->setMinPeople(5);
        $menu2->setStock(20);
        $menu2->setImageName('menu_vegetarien.jpg');
        $menu2->setConditions("Commande à passer au moins 3 jours avant la prestation.\nLes produits frais doivent être consommés dans les 24 heures suivant la livraison.");
        $menu2->addProduct($products[0]); // Bruschetta
        $menu2->addProduct($products[1]); // Rouleaux de Printemps
        $menu2->addDiet($diets['Végétarien']);
        $manager->persist($menu2);

        $menu3 = new Menu();
        $menu3->setName('Voyage Asiatique');
        $menu3->setDescription('Une entrée fraîche et un plat wok parfumé, pour un buffet dépaysant.');
        $menu3->setPrice(24.00);
        $menu3->setMinPeople(8);
        $menu3->setStock(12);
        $menu3->addProduct($products[1]); // Rouleaux de Printemps
        $menu3->addProduct($products[3]); // Pad Thai
        $menu3->setConditions("Commande à passer au moins 5 jours avant la prestation.\nLe wok est livré chaud dans un conteneur isotherme prêté par l'entreprise, à restituer sous 10 jours ouvrés.\nCe menu contient des arachides et du soja : prévenez-nous en cas d'allergie parmi vos convives.");
        $menu3->addTheme($themes['Asiatique']);
        $manager->persist($menu3);

        $menu4 = new Menu();
        $menu4->setName('Douceur Vegan');
        $menu4->setDescription('Un menu léger et 100% végétal, sans gluten, idéal pour un cocktail.');
        $menu4->setPrice(16.00);
        $menu4->setMinPeople(5);
        $menu4->setStock(25);
        $menu4->addProduct($products[5]); // Salade de Fruits
        $menu4->setConditions("Commande à passer au moins 48 heures avant la prestation.\nLes fruits sont préparés le matin même : à consommer le jour de la livraison.");
        $menu4->addTheme($themes['Français']);
        $menu4->addDiet($diets['Vegan']);
        $menu4->addDiet($diets['Sans Gluten']);
        $manager->persist($menu4);

        // Galeries de photos : plusieurs images par menu, prises parmi celles
        // deja presentes dans public/images/.
        $galleries = [
            [$menu1, ['images/menus/festin_italien.jpg', 'images/products/bruschetta.jpg', 'images/products/pizza_margherita.jpg', 'images/products/tiramisu.jpg']],
            [$menu2, ['images/menus/menu_vegetarien.jpg', 'images/products/spring_rolls.jpg']],
            [$menu3, ['images/products/pad_thai.jpg', 'images/products/spring_rolls.jpg']],
            [$menu4, ['images/products/fruit_salad.webp', 'images/products/fruit_salad.jpg']],
        ];
        foreach ($galleries as [$menu, $paths]) {
            foreach ($paths as $position => $path) {
                $image = new MenuImage();
                $image->setPath($path);
                $image->setAlt(sprintf('Photo %d du menu %s', $position + 1, $menu->getName()));
                $image->setPosition($position);
                $menu->addImage($image);
            }
        }

        // 5. Create Orders (History)
        //
        // Chaque commande porte un MENU, et non un plat isole : c'est le menu
        // que le client commande dans l'application. C'est aussi ce qui permet
        // au tableau de bord administrateur de comparer les menus entre eux.
        //
        // Les totaux suivent la regle de calcul de l'application :
        //   prix par personne x convives, - 10 % si le minimum est depasse de 5
        //   + livraison hors Bordeaux : 5 EUR + 0,59 EUR par km (voir OrderPricer).
        //
        // [menu, convives, jours dans le passe, statut, adresse, livraison, total]
        $orderData = [
            [$menu1, 16, 2,  'delivered', '24 cours de l\'Intendance, Bordeaux', 0.0,  316.80],
            [$menu1, 12, 9,  'completed', '8 avenue de la Marne, Mérignac',      9.72, 273.72],
            [$menu3, 10, 5,  'delivered', '15 quai des Chartrons, Bordeaux',     0.0,  240.00],
            [$menu2, 10, 14, 'completed', '3 rue Sainte-Catherine, Bordeaux',    0.0,  162.00],
            [$menu4, 6,  1,  'pending',   '52 avenue Roul, Talence',             7.95, 103.95],
        ];

        $createdOrders = [];

        foreach ($orderData as [$menu, $people, $daysAgo, $status, $address, $delivery, $total]) {
            $order = new Order();
            $order->setUser($customer);
            $order->setStatus($status);
            $order->setTotalPrice($total);
            $order->setCreatedAt(new \DateTime(sprintf('-%d days', $daysAgo)));
            $order->setNombrePersonne($people);
            $order->setAdressePrestation($address);
            $order->setPrixLivraison($delivery);
            $order->setEventDate(new \DateTime(sprintf('+%d days', $daysAgo + 3)));
            $order->setDeliveryTime(new \DateTime('19:30:00'));
            $order->setEquipmentLoan($status === 'delivered');
            $order->setEquipmentReturn(false);
            $manager->persist($order);

            $item = new OrderItem();
            $item->setOrderRef($order);
            $item->setMenu($menu);
            $item->setQuantity(1);
            $item->setPriceAtOrder((float) $menu->getPrice());
            $manager->persist($item);

            // Historique des statuts : on rejoue le parcours reel de la commande,
            // en espacant les etapes de quelques heures, pour que le suivi
            // affiche cote client soit credible.
            $parcours = match ($status) {
                'delivered' => ['pending', 'accepted', 'preparing', 'delivering', 'delivered'],
                'completed' => ['pending', 'accepted', 'preparing', 'delivering', 'delivered', 'completed'],
                'cancelled' => ['pending', 'cancelled'],
                default     => ['pending'],
            };

            $etape = new \DateTimeImmutable(sprintf('-%d days', $daysAgo));
            foreach ($parcours as $index => $etat) {
                $history = new OrderStatusHistory();
                $history->setOrderRef($order);
                $history->setStatus($etat);
                $history->setChangedAt($etape->modify(sprintf('+%d hours', $index * 5)));
                $history->setChangedBy($index === 0 ? $customer : $employee);
                $manager->persist($history);
            }

            $createdOrders[] = $order;
        }

        $review = new Review();
        $review->setOrderRef($createdOrders[0]);
        $review->setRating(5);
        $review->setComment('Excellent repas, arrivé chaud !');
        $review->setStatus('approved');
        $manager->persist($review);

        $review2 = new Review();
        $review2->setOrderRef($createdOrders[1]);
        $review2->setRating(4);
        $review2->setComment('Très bon, mais un peu de retard sur la livraison.');
        $review2->setStatus('approved');
        $manager->persist($review2);

        $manager->flush();
    }
}
