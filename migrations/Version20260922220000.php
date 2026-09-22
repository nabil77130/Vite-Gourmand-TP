<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cree la table order_status_history.
 *
 * Elle conserve tous les etats traverses par une commande, avec la date et
 * l'heure du changement et son auteur. L'enonce demande que le suivi d'une
 * commande "enumere tous les etats de sa commande suivi de la date et l'heure
 * de modification".
 */
final class Version20260922220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table order_status_history pour le suivi horodaté des commandes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE order_status_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            order_ref_id INTEGER NOT NULL,
            changed_by_id INTEGER DEFAULT NULL,
            status VARCHAR(30) NOT NULL,
            changed_at DATETIME NOT NULL,
            CONSTRAINT FK_471BB88CE238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
            CONSTRAINT FK_471BB88C828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_471BB88CE238517C ON order_status_history (order_ref_id)');
        $this->addSql('CREATE INDEX IDX_471BB88C828AD0A0 ON order_status_history (changed_by_id)');

        // Les commandes deja en base recoivent une premiere entree d'historique,
        // afin que leur suivi ne soit pas vide.
        $this->addSql('INSERT INTO order_status_history (order_ref_id, changed_by_id, status, changed_at)
                       SELECT id, user_id, status, created_at FROM "order"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE order_status_history');
    }
}
