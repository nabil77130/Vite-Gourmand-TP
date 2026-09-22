<?php

namespace App\Entity;

use App\Repository\MenuImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Photo de la galerie d'un menu.
 *
 * L'enonce demande qu'un menu dispose d'"une galerie d'image" : un menu peut
 * donc avoir plusieurs photos, affichees dans l'ordre de leur position.
 *
 * Le chemin est relatif au dossier public/ (ex. "uploads/menus/abc.jpg"), ce
 * qui permet d'utiliser aussi bien les photos envoyees depuis l'espace employe
 * que les images deja presentes dans public/images/.
 */
#[ORM\Entity(repositoryClass: MenuImageRepository::class)]
#[ORM\Table(name: 'menu_image')]
class MenuImage
{
    /** Dossier des photos envoyees depuis l'espace employe, relatif a public/. */
    public const UPLOAD_DIR = 'uploads/menus';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Menu $menu = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    /**
     * Texte alternatif, lu par les lecteurs d'ecran (RGAA, critere 1.1).
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Vrai pour une photo envoyee depuis l'espace employe : seul ce type de
     * fichier peut etre efface du disque lors de la suppression.
     */
    public function isUploaded(): bool
    {
        return str_starts_with((string) $this->path, self::UPLOAD_DIR . '/');
    }
}
