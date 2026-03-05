<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305202949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE band_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, band_id INT DEFAULT NULL, INDEX IDX_39DFBAC33DA5256D (image_id), INDEX IDX_39DFBAC349ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_F2DE3183232D562B (object_id), INDEX indie_band_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE band_image ADD CONSTRAINT FK_39DFBAC33DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE band_image ADD CONSTRAINT FK_39DFBAC349ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id)');
        $this->addSql('ALTER TABLE indie_band_translation ADD CONSTRAINT FK_F2DE3183232D562B FOREIGN KEY (object_id) REFERENCES indie_band (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band_image DROP FOREIGN KEY FK_39DFBAC33DA5256D');
        $this->addSql('ALTER TABLE band_image DROP FOREIGN KEY FK_39DFBAC349ABEB17');
        $this->addSql('ALTER TABLE indie_band_translation DROP FOREIGN KEY FK_F2DE3183232D562B');
        $this->addSql('DROP TABLE band_image');
        $this->addSql('DROP TABLE indie_band_translation');
    }
}
