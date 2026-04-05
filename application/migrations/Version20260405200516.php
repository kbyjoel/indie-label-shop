<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405200516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_tax_category (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_221EB0BE77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_tax_rate (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, amount NUMERIC(10, 4) NOT NULL, included_in_price TINYINT NOT NULL, calculator VARCHAR(255) NOT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, category_id INT NOT NULL, zone_id INT NOT NULL, UNIQUE INDEX UNIQ_3CD86B2E77153098 (code), INDEX IDX_3CD86B2E12469DE2 (category_id), INDEX IDX_3CD86B2E9F2C3FAB (zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sylius_tax_rate ADD CONSTRAINT FK_3CD86B2E12469DE2 FOREIGN KEY (category_id) REFERENCES sylius_tax_category (id)');
        $this->addSql('ALTER TABLE sylius_tax_rate ADD CONSTRAINT FK_3CD86B2E9F2C3FAB FOREIGN KEY (zone_id) REFERENCES sylius_zone (id)');
        $this->addSql('ALTER TABLE sylius_product_variant ADD tax_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD CONSTRAINT FK_A29B5239DF894ED FOREIGN KEY (tax_category_id) REFERENCES sylius_tax_category (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A29B5239DF894ED ON sylius_product_variant (tax_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_tax_rate DROP FOREIGN KEY FK_3CD86B2E12469DE2');
        $this->addSql('ALTER TABLE sylius_tax_rate DROP FOREIGN KEY FK_3CD86B2E9F2C3FAB');
        $this->addSql('DROP TABLE sylius_tax_category');
        $this->addSql('DROP TABLE sylius_tax_rate');
        $this->addSql('ALTER TABLE sylius_product_variant DROP FOREIGN KEY FK_A29B5239DF894ED');
        $this->addSql('DROP INDEX IDX_A29B5239DF894ED ON sylius_product_variant');
        $this->addSql('ALTER TABLE sylius_product_variant DROP tax_category_id');
    }
}
