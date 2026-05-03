<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503154636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_address ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_address ADD CONSTRAINT FK_B97FF0589395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B97FF0589395C3F3 ON sylius_address (customer_id)');
        $this->addSql('ALTER TABLE sylius_customer ADD default_address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD CONSTRAINT FK_7E82D5E6BD94FB16 FOREIGN KEY (default_address_id) REFERENCES sylius_address (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7E82D5E6BD94FB16 ON sylius_customer (default_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_address DROP FOREIGN KEY FK_B97FF0589395C3F3');
        $this->addSql('DROP INDEX IDX_B97FF0589395C3F3 ON sylius_address');
        $this->addSql('ALTER TABLE sylius_address DROP customer_id');
        $this->addSql('ALTER TABLE sylius_customer DROP FOREIGN KEY FK_7E82D5E6BD94FB16');
        $this->addSql('DROP INDEX IDX_7E82D5E6BD94FB16 ON sylius_customer');
        $this->addSql('ALTER TABLE sylius_customer DROP default_address_id');
    }
}
