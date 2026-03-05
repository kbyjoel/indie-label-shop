<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305203408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product ADD band_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7449ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_677B9B7449ABEB17 ON sylius_product (band_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B7449ABEB17');
        $this->addSql('DROP INDEX IDX_677B9B7449ABEB17 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP band_id');
    }
}
