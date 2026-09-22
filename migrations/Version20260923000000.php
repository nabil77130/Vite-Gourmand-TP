<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cree la table menu_image : la galerie de photos d'un menu.
 *
 * L'enonce demande qu'un menu dispose d'"une galerie d'image". L'ancienne
 * colonne menu.image_name ne pouvait porter qu'une seule photo.
 */
final class Version20260923000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table menu_image (galerie de photos des menus)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE menu_image (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            menu_id INTEGER NOT NULL,
            path VARCHAR(255) NOT NULL,
            alt VARCHAR(255) DEFAULT NULL,
            position INTEGER NOT NULL,
            CONSTRAINT FK_54912738CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_54912738CCD7E912 ON menu_image (menu_id)');

        // L'image unique deja associee a un menu devient la premiere photo de
        // sa galerie : rien n'est perdu.
        $this->addSql("INSERT INTO menu_image (menu_id, path, alt, position)
                       SELECT id, 'images/menus/' || image_name, 'Photo du menu ' || name, 0
                       FROM menu WHERE image_name IS NOT NULL AND image_name <> ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE menu_image');
    }
}
