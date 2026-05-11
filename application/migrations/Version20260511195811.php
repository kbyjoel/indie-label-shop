<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511195811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipment ADD pickup_point_external_id VARCHAR(128) DEFAULT NULL, ADD pickup_point_name VARCHAR(255) DEFAULT NULL, ADD pickup_point_address VARCHAR(255) DEFAULT NULL, ADD pickup_point_postal_code VARCHAR(10) DEFAULT NULL, ADD pickup_point_city VARCHAR(128) DEFAULT NULL, ADD pickup_point_country_code VARCHAR(2) DEFAULT NULL, ADD label_url VARCHAR(512) DEFAULT NULL, ADD label_generated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD gateway_code VARCHAR(64) DEFAULT NULL, ADD gateway_config JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipment DROP pickup_point_external_id, DROP pickup_point_name, DROP pickup_point_address, DROP pickup_point_postal_code, DROP pickup_point_city, DROP pickup_point_country_code, DROP label_url, DROP label_generated_at');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP gateway_code, DROP gateway_config');
    }
}
