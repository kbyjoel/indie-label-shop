<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308204805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_album_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_C093DBF1232D562B (object_id), INDEX indie_album_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_album_translation ADD CONSTRAINT FK_C093DBF1232D562B FOREIGN KEY (object_id) REFERENCES indie_album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_album ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indie_album_translation DROP FOREIGN KEY FK_C093DBF1232D562B');
        $this->addSql('DROP TABLE indie_album_translation');
        $this->addSql('ALTER TABLE indie_album DROP description');
    }
}
