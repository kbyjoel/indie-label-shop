<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503152818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add currency_code column to sylius_order and backfill from channel baseCurrency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order ADD currency_code VARCHAR(3) DEFAULT NULL');

        $this->addSql("
            UPDATE sylius_order o
            JOIN sylius_channel ch ON ch.id = o.channel_id
            JOIN sylius_currency cu ON cu.id = ch.default_currency_id
            SET o.currency_code = cu.code
            WHERE o.currency_code IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order DROP currency_code');
    }
}
