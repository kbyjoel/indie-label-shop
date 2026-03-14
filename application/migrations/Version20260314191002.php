<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260314191002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE release_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, release_id INT DEFAULT NULL, INDEX IDX_46A376283DA5256D (image_id), UNIQUE INDEX UNIQ_46A37628B12A727D (release_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE release_image_crop (filter VARCHAR(255) NOT NULL, crop VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_721FDDBF3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE release_image ADD CONSTRAINT FK_46A376283DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE release_image ADD CONSTRAINT FK_46A37628B12A727D FOREIGN KEY (release_id) REFERENCES indie_release (id)');
        $this->addSql('ALTER TABLE release_image_crop ADD CONSTRAINT FK_721FDDBF3DA5256D FOREIGN KEY (image_id) REFERENCES release_image (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE release_image DROP FOREIGN KEY FK_46A376283DA5256D');
        $this->addSql('ALTER TABLE release_image DROP FOREIGN KEY FK_46A37628B12A727D');
        $this->addSql('ALTER TABLE release_image_crop DROP FOREIGN KEY FK_721FDDBF3DA5256D');
        $this->addSql('DROP TABLE release_image');
        $this->addSql('DROP TABLE release_image_crop');
    }
}
