<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305205825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_option ADD code VARCHAR(255) NOT NULL, ADD position INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4C0EBEF77153098 ON sylius_product_option (code)');
        $this->addSql('ALTER TABLE sylius_product_option_value ADD code VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7FF7D4B77153098 ON sylius_product_option_value (code)');
        $this->addSql('ALTER TABLE sylius_product_option_value_translation ADD value VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E4C0EBEF77153098 ON sylius_product_option');
        $this->addSql('ALTER TABLE sylius_product_option DROP code, DROP position, DROP created_at, DROP updated_at');
        $this->addSql('DROP INDEX UNIQ_F7FF7D4B77153098 ON sylius_product_option_value');
        $this->addSql('ALTER TABLE sylius_product_option_value DROP code');
        $this->addSql('ALTER TABLE sylius_product_option_value_translation DROP value');
    }
}
