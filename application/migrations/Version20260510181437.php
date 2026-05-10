<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510181437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD checkout_completed_at DATETIME DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD items_total INT NOT NULL, ADD adjustments_total INT NOT NULL, ADD checkout_state VARCHAR(255) NOT NULL, ADD payment_state VARCHAR(255) NOT NULL, ADD shipping_state VARCHAR(255) NOT NULL, ADD locale_code VARCHAR(255) DEFAULT NULL, ADD customer_ip VARCHAR(255) DEFAULT NULL, ADD created_by_guest TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order DROP created_at, DROP updated_at, DROP checkout_completed_at, DROP notes, DROP items_total, DROP adjustments_total, DROP checkout_state, DROP payment_state, DROP shipping_state, DROP locale_code, DROP customer_ip, DROP created_by_guest');
    }
}
