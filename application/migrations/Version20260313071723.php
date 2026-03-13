<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313071723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_track_master_file (title VARCHAR(255) DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, track_id INT DEFAULT NULL, INDEX IDX_D962FA5D93CB796C (file_id), UNIQUE INDEX UNIQ_D962FA5D5ED23C43 (track_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE indie_track_master_file ADD CONSTRAINT FK_D962FA5D93CB796C FOREIGN KEY (file_id) REFERENCES aropixel_file (id)');
        $this->addSql('ALTER TABLE indie_track_master_file ADD CONSTRAINT FK_D962FA5D5ED23C43 FOREIGN KEY (track_id) REFERENCES indie_track (id)');
        $this->addSql('ALTER TABLE indie_track_wav_file DROP FOREIGN KEY `FK_D65465885ED23C43`');
        $this->addSql('ALTER TABLE indie_track_wav_file DROP FOREIGN KEY `FK_D654658893CB796C`');
        $this->addSql('DROP TABLE indie_track_wav_file');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indie_track_wav_file (title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, alt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, track_id INT DEFAULT NULL, INDEX IDX_D654658893CB796C (file_id), UNIQUE INDEX UNIQ_D65465885ED23C43 (track_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE indie_track_wav_file ADD CONSTRAINT `FK_D65465885ED23C43` FOREIGN KEY (track_id) REFERENCES indie_track (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE indie_track_wav_file ADD CONSTRAINT `FK_D654658893CB796C` FOREIGN KEY (file_id) REFERENCES aropixel_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE indie_track_master_file DROP FOREIGN KEY FK_D962FA5D93CB796C');
        $this->addSql('ALTER TABLE indie_track_master_file DROP FOREIGN KEY FK_D962FA5D5ED23C43');
        $this->addSql('DROP TABLE indie_track_master_file');
    }
}
