<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316132715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__i23_paniers AS SELECT id, produit_id, client_id, quantite FROM i23_paniers');
        $this->addSql('DROP TABLE i23_paniers');
        $this->addSql('CREATE TABLE i23_paniers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER NOT NULL, client_id INTEGER NOT NULL, quantite INTEGER NOT NULL, CONSTRAINT FK_62571961F347EFB FOREIGN KEY (produit_id) REFERENCES i23_produits (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6257196119EB6921 FOREIGN KEY (client_id) REFERENCES i23_clients (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO i23_paniers (id, produit_id, client_id, quantite) SELECT id, produit_id, client_id, quantite FROM __temp__i23_paniers');
        $this->addSql('DROP TABLE __temp__i23_paniers');
        $this->addSql('CREATE INDEX IDX_6257196119EB6921 ON i23_paniers (client_id)');
        $this->addSql('CREATE INDEX IDX_62571961F347EFB ON i23_paniers (produit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE i23_paniers ADD COLUMN prix DOUBLE PRECISION NOT NULL');
    }
}
