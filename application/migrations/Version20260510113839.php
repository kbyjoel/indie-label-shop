<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510113839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_band_video_clip (id INT AUTO_INCREMENT NOT NULL, iframe_code LONGTEXT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, band_id INT NOT NULL, INDEX IDX_747DDC1449ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_band_video_clip ADD CONSTRAINT FK_747DDC1449ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indie_band_video_clip DROP FOREIGN KEY FK_747DDC1449ABEB17');
        $this->addSql('DROP TABLE indie_band_video_clip');
    }
}
