<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406125952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_shipping_method_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_2B37DB3D2C2AC5D3 (translatable_id), UNIQUE INDEX sylius_shipping_method_translation_uniq_trans (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sylius_shipping_method_translation ADD CONSTRAINT FK_2B37DB3D2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_shipping_method (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method_translation DROP FOREIGN KEY FK_2B37DB3D2C2AC5D3');
        $this->addSql('DROP TABLE sylius_shipping_method_translation');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD name VARCHAR(255) NOT NULL');
    }
}
