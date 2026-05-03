<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503152007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure EUR currency exists and is linked as baseCurrency of the WEB channel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO sylius_currency (code) VALUES ('EUR')");

        $this->addSql("
            UPDATE sylius_channel
            SET default_currency_id = (SELECT id FROM sylius_currency WHERE code = 'EUR' LIMIT 1)
            WHERE default_currency_id IS NULL
               OR NOT EXISTS (SELECT 1 FROM sylius_currency WHERE id = sylius_channel.default_currency_id)
        ");

        $this->addSql("
            INSERT IGNORE INTO sylius_channel_currencies (channel_id, currency_id)
            SELECT ch.id, cu.id
            FROM sylius_channel ch, sylius_currency cu
            WHERE cu.code = 'EUR'
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
