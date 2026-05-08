<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508135649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_order_promotion (order_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_572917178D9F6D38 (order_id), INDEX IDX_57291717139DF194 (promotion_id), PRIMARY KEY (order_id, promotion_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sylius_order_promotion ADD CONSTRAINT FK_572917178D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order_promotion ADD CONSTRAINT FK_57291717139DF194 FOREIGN KEY (promotion_id) REFERENCES sylius_promotion (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order_promotion DROP FOREIGN KEY FK_572917178D9F6D38');
        $this->addSql('ALTER TABLE sylius_order_promotion DROP FOREIGN KEY FK_57291717139DF194');
        $this->addSql('DROP TABLE sylius_order_promotion');
    }
}
