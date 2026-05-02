<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502090917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_product_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, product_id INT DEFAULT NULL, INDEX IDX_1EC55A4D3DA5256D (image_id), UNIQUE INDEX UNIQ_1EC55A4D4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_product_image ADD CONSTRAINT FK_1EC55A4D3DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE indie_product_image ADD CONSTRAINT FK_1EC55A4D4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indie_product_image DROP FOREIGN KEY FK_1EC55A4D3DA5256D');
        $this->addSql('ALTER TABLE indie_product_image DROP FOREIGN KEY FK_1EC55A4D4584665A');
        $this->addSql('DROP TABLE indie_product_image');
    }
}
