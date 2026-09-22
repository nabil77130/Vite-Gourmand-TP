<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les relations Menu <-> Theme et Menu <-> Diet.
 *
 * Jusqu'ici les thematiques et les regimes alimentaires n'etaient rattaches
 * qu'aux plats (product_theme / product_diet). Pour pouvoir filtrer la carte
 * par thematique et par regime, il faut pouvoir qualifier un menu directement.
 */
final class Version20260921070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creation des tables de liaison menu_theme et menu_diet pour les filtres de la carte';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE menu_theme (menu_id INTEGER NOT NULL, theme_id INTEGER NOT NULL, PRIMARY KEY (menu_id, theme_id), CONSTRAINT FK_6D9C46FCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6D9C46F59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6D9C46FCCD7E912 ON menu_theme (menu_id)');
        $this->addSql('CREATE INDEX IDX_6D9C46F59027487 ON menu_theme (theme_id)');

        $this->addSql('CREATE TABLE menu_diet (menu_id INTEGER NOT NULL, diet_id INTEGER NOT NULL, PRIMARY KEY (menu_id, diet_id), CONSTRAINT FK_55AB956ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_55AB956EE1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_55AB956ECCD7E912 ON menu_diet (menu_id)');
        $this->addSql('CREATE INDEX IDX_55AB956EE1E13ACE ON menu_diet (diet_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE menu_theme');
        $this->addSql('DROP TABLE menu_diet');
    }
}
