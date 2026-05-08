<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508204016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_concert (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, city VARCHAR(255) NOT NULL, venue VARCHAR(255) NOT NULL, status VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, band_id INT NOT NULL, INDEX IDX_586C1B0F49ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_concert ADD CONSTRAINT FK_586C1B0F49ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indie_concert DROP FOREIGN KEY FK_586C1B0F49ABEB17');
        $this->addSql('DROP TABLE indie_concert');
    }
}
