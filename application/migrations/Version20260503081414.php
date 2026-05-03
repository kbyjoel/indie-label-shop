<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503081414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_download_token (id INT AUTO_INCREMENT NOT NULL, token_value VARCHAR(36) NOT NULL, format VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, s3_path VARCHAR(512) DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, order_item_id INT NOT NULL, UNIQUE INDEX UNIQ_E153E535BEA95C75 (token_value), INDEX IDX_E153E535E415FB15 (order_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_download_token ADD CONSTRAINT FK_E153E535E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indie_download_token DROP FOREIGN KEY FK_E153E535E415FB15');
        $this->addSql('DROP TABLE indie_download_token');
    }
}
