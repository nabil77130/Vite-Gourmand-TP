<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\ManyToMany(targetEntity: Product::class)]
    private Collection $products; // Composed of several products (Starter, Main, Dessert)

    #[ORM\Column(nullable: true)]
    private ?int $minPeople = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    /**
     * Conditions propres au menu : delai de commande, precautions de stockage,
     * materiel prete, etc.
     *
     * L'enonce impose qu'elles soient mises bien en evidence avant la commande,
     * "afin d'eviter que le client puisse se plaindre qu'il n'a pas vu
     * l'information".
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conditions = null;

    /**
     * Thematiques du menu (Italien, Asiatique...) : sert aux filtres de la carte.
     */
    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'menus')]
    private Collection $themes;

    /**
     * Regimes alimentaires du menu (Vegetarien, Sans Gluten...) : sert aux filtres de la carte.
     */
    #[ORM\ManyToMany(targetEntity: Diet::class, inversedBy: 'menus')]
    private Collection $diets;

    /**
     * Galerie de photos du menu, dans l'ordre d'affichage.
     */
    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->diets = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getMinPeople(): ?int
    {
        return $this->minPeople;
    }

    public function setMinPeople(?int $minPeople): static
    {
        $this->minPeople = $minPeople;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * Le stock represente le nombre de commandes encore possibles pour ce menu
     * ("il reste 5 commandes possibles", selon l'enonce). Un stock nul (null)
     * signifie que le menu n'est pas limite.
     */
    public function isAvailable(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    /**
     * Consomme une commande possible. Sans effet sur un menu non limite.
     */
    public function decrementStock(): static
    {
        if ($this->stock !== null && $this->stock > 0) {
            --$this->stock;
        }

        return $this;
    }

    /**
     * Rend une commande possible, par exemple apres une annulation.
     */
    public function incrementStock(): static
    {
        if ($this->stock !== null) {
            ++$this->stock;
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(?string $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        $this->themes->removeElement($theme);

        return $this;
    }

    /**
     * @return Collection<int, Diet>
     */
    public function getDiets(): Collection
    {
        return $this->diets;
    }

    public function addDiet(Diet $diet): static
    {
        if (!$this->diets->contains($diet)) {
            $this->diets->add($diet);
        }

        return $this;
    }

    public function removeDiet(Diet $diet): static
    {
        $this->diets->removeElement($diet);

        return $this;
    }

    /**
     * @return Collection<int, MenuImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(MenuImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setMenu($this);
        }

        return $this;
    }

    public function removeImage(MenuImage $image): static
    {
        if ($this->images->removeElement($image) && $image->getMenu() === $this) {
            $image->setMenu(null);
        }

        return $this;
    }

    /**
     * Position a donner a la prochaine photo ajoutee a la galerie.
     */
    public function getNextImagePosition(): int
    {
        $max = -1;
        foreach ($this->images as $image) {
            $max = max($max, $image->getPosition());
        }

        return $max + 1;
    }
}
