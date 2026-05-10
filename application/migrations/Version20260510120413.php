<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510120413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aropixel_page (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, type VARCHAR(100) NOT NULL, title VARCHAR(255) DEFAULT NULL, sub_title VARCHAR(255) DEFAULT NULL, static_code VARCHAR(50) DEFAULT NULL, is_deletable TINYINT NOT NULL, excerpt LONGTEXT DEFAULT NULL, html_content LONGTEXT DEFAULT NULL, json_content LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, meta_keywords VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, publish_at DATETIME DEFAULT NULL, publish_until DATETIME DEFAULT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_87C59DCBECBB692D (static_code), INDEX IDX_87C59DCB727ACA70 (parent_id), INDEX IDX_87C59DCB8CDE5729 (type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_page_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(20) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_45C39FBF232D562B (object_id), INDEX page_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, title VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, meta_keywords VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, publish_at DATETIME DEFAULT NULL, publish_until DATETIME DEFAULT NULL, image_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_C94547663DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post_category_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(20) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_60FC0DC5232D562B (object_id), INDEX post_category_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_49CF851D3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post_image_crop (filter VARCHAR(255) NOT NULL, crop VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_49587D8B3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_post_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(20) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_BE3F4BE2232D562B (object_id), INDEX post_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_photo (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, band_id INT DEFAULT NULL, INDEX IDX_EBB325163DA5256D (image_id), INDEX IDX_EBB3251649ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE aropixel_page ADD CONSTRAINT FK_87C59DCB727ACA70 FOREIGN KEY (parent_id) REFERENCES aropixel_page (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE aropixel_page_translation ADD CONSTRAINT FK_45C39FBF232D562B FOREIGN KEY (object_id) REFERENCES aropixel_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aropixel_post ADD CONSTRAINT FK_C94547663DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_post_image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE aropixel_post_category_translation ADD CONSTRAINT FK_60FC0DC5232D562B FOREIGN KEY (object_id) REFERENCES aropixel_post_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aropixel_post_image ADD CONSTRAINT FK_49CF851D3DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE aropixel_post_image_crop ADD CONSTRAINT FK_49587D8B3DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_post_image (id)');
        $this->addSql('ALTER TABLE aropixel_post_translation ADD CONSTRAINT FK_BE3F4BE2232D562B FOREIGN KEY (object_id) REFERENCES aropixel_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_band_photo ADD CONSTRAINT FK_EBB325163DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE indie_band_photo ADD CONSTRAINT FK_EBB3251649ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aropixel_page DROP FOREIGN KEY FK_87C59DCB727ACA70');
        $this->addSql('ALTER TABLE aropixel_page_translation DROP FOREIGN KEY FK_45C39FBF232D562B');
        $this->addSql('ALTER TABLE aropixel_post DROP FOREIGN KEY FK_C94547663DA5256D');
        $this->addSql('ALTER TABLE aropixel_post_category_translation DROP FOREIGN KEY FK_60FC0DC5232D562B');
        $this->addSql('ALTER TABLE aropixel_post_image DROP FOREIGN KEY FK_49CF851D3DA5256D');
        $this->addSql('ALTER TABLE aropixel_post_image_crop DROP FOREIGN KEY FK_49587D8B3DA5256D');
        $this->addSql('ALTER TABLE aropixel_post_translation DROP FOREIGN KEY FK_BE3F4BE2232D562B');
        $this->addSql('ALTER TABLE indie_band_photo DROP FOREIGN KEY FK_EBB325163DA5256D');
        $this->addSql('ALTER TABLE indie_band_photo DROP FOREIGN KEY FK_EBB3251649ABEB17');
        $this->addSql('DROP TABLE aropixel_page');
        $this->addSql('DROP TABLE aropixel_page_translation');
        $this->addSql('DROP TABLE aropixel_post');
        $this->addSql('DROP TABLE aropixel_post_category');
        $this->addSql('DROP TABLE aropixel_post_category_translation');
        $this->addSql('DROP TABLE aropixel_post_image');
        $this->addSql('DROP TABLE aropixel_post_image_crop');
        $this->addSql('DROP TABLE aropixel_post_translation');
        $this->addSql('DROP TABLE indie_band_photo');
    }
}
