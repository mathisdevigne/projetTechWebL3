<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316083706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE i23_paniers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER NOT NULL, client_id INTEGER NOT NULL, quantite INTEGER NOT NULL, prix DOUBLE PRECISION NOT NULL, CONSTRAINT FK_62571961F347EFB FOREIGN KEY (produit_id) REFERENCES i23_produits (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6257196119EB6921 FOREIGN KEY (client_id) REFERENCES i23_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_62571961F347EFB ON i23_paniers (produit_id)');
        $this->addSql('CREATE INDEX IDX_6257196119EB6921 ON i23_paniers (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE i23_paniers');
    }
}
