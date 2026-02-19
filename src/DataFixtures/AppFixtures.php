<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\Diet;
use App\Entity\Horaire;
use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderItem;
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
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setFirstName('Julie');
        $admin->setLastName('Manager');
        $admin->setPhone('0600000000');
        $manager->persist($admin);

        $employee = new User();
        $employee->setEmail('employee@vite-gourmand.com');
        $employee->setRoles(['ROLE_EMPLOYEE']);
        $employee->setPassword($this->userPasswordHasher->hashPassword($employee, 'password'));
        $employee->setFirstName('Marc');
        $employee->setLastName('Employé');
        $employee->setPhone('0611111111');
        $manager->persist($employee);

        $customer = new User();
        $customer->setEmail('user@vite-gourmand.com');
        $customer->setRoles(['ROLE_USER']);
        $customer->setPassword($this->userPasswordHasher->hashPassword($customer, 'password'));
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
        $menu1->addProduct($products[0]); // Bruschetta
        $menu1->addProduct($products[2]); // Pizza
        $menu1->addProduct($products[4]); // Tiramisu
        $manager->persist($menu1);

        $menu2 = new Menu();
        $menu2->setName('Menu Végétarien');
        $menu2->setDescription('Un menu 100% végétarien, frais et savoureux.');
        $menu2->setPrice(18.00);
        $menu2->setMinPeople(5);
        $menu2->setStock(20);
        $menu2->setImageName('menu_vegetarien.jpg');
        $menu2->addProduct($products[0]); // Bruschetta
        $menu2->addProduct($products[1]); // Salade César
        $manager->persist($menu2);

        // 5. Create Orders (History)
        $order = new Order();
        $order->setUser($customer);
        $order->setStatus('delivered');
        $order->setTotalPrice(25.50);
        $order->setcreatedAt(new \DateTime('-2 days'));
        // Logistics
        $order->setEventDate(new \DateTime('+5 days'));
        $order->setDeliveryTime(new \DateTime('19:30:00'));
        $order->setEquipmentLoan(true);
        $order->setEquipmentReturn(false);
        $manager->persist($order);

        $orderItem1 = new OrderItem();
        $orderItem1->setOrderRef($order);
        $orderItem1->setProduct($products[2]); // Pizza
        $orderItem1->setQuantity(2);
        $orderItem1->setPriceAtOrder(12.00);
        $manager->persist($orderItem1);

        $review = new Review();
        $review->setOrderRef($order);
        $review->setRating(5);
        $review->setComment('Excellent repas, arrivé chaud !');
        $review->setStatus('approved');
        $manager->persist($review);

        // Another order and review
        $order2 = new Order();
        $order2->setUser($customer);
        $order2->setStatus('completed');
        $order2->setTotalPrice(45.00);
        $order2->setCreatedAt(new \DateTime('-5 days'));
        $manager->persist($order2);

        $review2 = new Review();
        $review2->setOrderRef($order2);
        $review2->setRating(4);
        $review2->setComment('Très bon, mais un peu de retard sur la livraison.');
        $review2->setStatus('approved');
        $manager->persist($review2);

        $manager->flush();
    }
}
