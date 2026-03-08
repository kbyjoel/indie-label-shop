<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308173835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aropixel_admin_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, enabled TINYINT NOT NULL, initialized TINYINT NOT NULL, password_attempts INT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_password_update DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B6635904E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_admin_user_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_9D7DD203DA5256D (image_id), UNIQUE INDEX UNIQ_9D7DD20A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_file (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) NOT NULL, extension VARCHAR(20) NOT NULL, public TINYINT NOT NULL, import LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE aropixel_image (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, extension VARCHAR(20) NOT NULL, import LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_album (slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, catalog_number VARCHAR(100) DEFAULT NULL, release_date DATE DEFAULT NULL, status VARCHAR(20) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_album_similar (album_source INT NOT NULL, album_target INT NOT NULL, INDEX IDX_E4C840C18DF142C4 (album_source), INDEX IDX_E4C840C19414124B (album_target), PRIMARY KEY (album_source, album_target)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_album_artist (album_id INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_1D5D4DBB1137ABCF (album_id), INDEX IDX_1D5D4DBBB7970CF8 (artist_id), PRIMARY KEY (album_id, artist_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_artist (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, address LONGTEXT NOT NULL, zip_code VARCHAR(30) NOT NULL, city VARCHAR(255) NOT NULL, phone VARCHAR(30) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_artist (band_id INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_E61EBF2149ABEB17 (band_id), INDEX IDX_E61EBF21B7970CF8 (artist_id), PRIMARY KEY (band_id, artist_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_image (title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, attr_title VARCHAR(255) DEFAULT NULL, attr_alt VARCHAR(255) DEFAULT NULL, attr_class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, band_id INT DEFAULT NULL, INDEX IDX_3A39A5513DA5256D (image_id), INDEX IDX_3A39A55149ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_image_crop (filter VARCHAR(255) NOT NULL, crop VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_C13242AA3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_band_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX IDX_F2DE3183232D562B (object_id), INDEX indie_band_translation_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_media (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, is_digital TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_release (title VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, status VARCHAR(20) NOT NULL, media_id INT NOT NULL, id INT NOT NULL, INDEX IDX_13571AC0EA9FDD75 (media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_track (title VARCHAR(255) NOT NULL, duration VARCHAR(20) DEFAULT NULL, isrc VARCHAR(20) DEFAULT NULL, lyrics LONGTEXT DEFAULT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_tracklist (id INT AUTO_INCREMENT NOT NULL, position INT NOT NULL, album_id INT NOT NULL, track_id INT NOT NULL, INDEX IDX_DD01D5E51137ABCF (album_id), INDEX IDX_DD01D5E55ED23C43 (track_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indie_tracklist_release (tracklist_id INT NOT NULL, release_id INT NOT NULL, INDEX IDX_62FD8DCA8C5F30E1 (tracklist_id), INDEX IDX_62FD8DCAB12A727D (release_id), PRIMARY KEY (tracklist_id, release_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_address (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postcode VARCHAR(255) DEFAULT NULL, country_code VARCHAR(255) DEFAULT NULL, province_code VARCHAR(255) DEFAULT NULL, province_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_channel (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, hostname VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_16C8119E77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_country (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT NOT NULL, UNIQUE INDEX UNIQ_E74256BF77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_customer (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_7E82D5E6E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_order (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, total INT NOT NULL, customer_id INT DEFAULT NULL, channel_id INT NOT NULL, shipping_address_id INT DEFAULT NULL, billing_address_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6196A1F996901F54 (number), INDEX IDX_6196A1F99395C3F3 (customer_id), INDEX IDX_6196A1F972F5A1AA (channel_id), INDEX IDX_6196A1F94D4CFF2B (shipping_address_id), INDEX IDX_6196A1F979D0C0E4 (billing_address_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_order_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price INT NOT NULL, total INT NOT NULL, order_id INT NOT NULL, variant_id INT NOT NULL, INDEX IDX_77B587ED8D9F6D38 (order_id), INDEX IDX_77B587ED3B69A9AF (variant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_order_item_unit (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, shipment_id INT DEFAULT NULL, INDEX IDX_82BF226EE415FB15 (order_item_id), INDEX IDX_82BF226E7BE036FC (shipment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_payment (id INT AUTO_INCREMENT NOT NULL, state VARCHAR(255) NOT NULL, amount INT NOT NULL, currency_code VARCHAR(255) NOT NULL, order_id INT NOT NULL, method_id INT NOT NULL, INDEX IDX_D9191BD48D9F6D38 (order_id), INDEX IDX_D9191BD419883967 (method_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_payment_method (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT NOT NULL, UNIQUE INDEX UNIQ_A75B0B0D77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, band_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_677B9B7477153098 (code), INDEX IDX_677B9B7449ABEB17 (band_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_channels (product_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_F9EF269B4584665A (product_id), INDEX IDX_F9EF269B72F5A1AA (channel_id), PRIMARY KEY (product_id, channel_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_options (product_id INT NOT NULL, option_id INT NOT NULL, INDEX IDX_2B5FF0094584665A (product_id), INDEX IDX_2B5FF009A7C41D6F (option_id), PRIMARY KEY (product_id, option_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_E4C0EBEF77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_CBA491AD2C2AC5D3 (translatable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_value (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, option_id INT NOT NULL, UNIQUE INDEX UNIQ_F7FF7D4B77153098 (code), INDEX IDX_F7FF7D4BA7C41D6F (option_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_value_translation (id INT AUTO_INCREMENT NOT NULL, value VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_8D4382DC2C2AC5D3 (translatable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, meta_keywords LONGTEXT DEFAULT NULL, meta_description LONGTEXT DEFAULT NULL, short_description VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, UNIQUE INDEX UNIQ_105A908989D9B62 (slug), INDEX IDX_105A9082C2AC5D3 (translatable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_variant (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, position INT DEFAULT NULL, on_hold INT NOT NULL, on_hand INT NOT NULL, tracked TINYINT NOT NULL, version INT NOT NULL, product_id INT NOT NULL, discr VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A29B52377153098 (code), INDEX IDX_A29B5234584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_variant_option_value (variant_id INT NOT NULL, option_value_id INT NOT NULL, INDEX IDX_76CDAFA13B69A9AF (variant_id), INDEX IDX_76CDAFA1D957CA06 (option_value_id), PRIMARY KEY (variant_id, option_value_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_promotion (id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F157396377153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_province (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, abbreviation VARCHAR(255) DEFAULT NULL, country_id INT NOT NULL, INDEX IDX_B5618FE4F92F3E70 (country_id), UNIQUE INDEX province_code_country_idx (code, country_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_shipment (id INT AUTO_INCREMENT NOT NULL, state VARCHAR(255) DEFAULT NULL, tracking VARCHAR(255) DEFAULT NULL, order_id INT NOT NULL, method_id INT NOT NULL, INDEX IDX_FD707B338D9F6D38 (order_id), INDEX IDX_FD707B3319883967 (method_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_shipping_method (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT NOT NULL, zone_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5FB0EE1177153098 (code), INDEX IDX_5FB0EE119F2C3FAB (zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_zone (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, scope VARCHAR(255) NOT NULL, priority INT NOT NULL, UNIQUE INDEX UNIQ_7BE2258E77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_zone_member (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, zone_id INT NOT NULL, INDEX IDX_E8B5ABF39F2C3FAB (zone_id), UNIQUE INDEX zone_member_code_zone_idx (code, zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE aropixel_admin_user_image ADD CONSTRAINT FK_9D7DD203DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE aropixel_admin_user_image ADD CONSTRAINT FK_9D7DD20A76ED395 FOREIGN KEY (user_id) REFERENCES aropixel_admin_user (id)');
        $this->addSql('ALTER TABLE indie_album ADD CONSTRAINT FK_9AF70694BF396750 FOREIGN KEY (id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_album_similar ADD CONSTRAINT FK_E4C840C18DF142C4 FOREIGN KEY (album_source) REFERENCES indie_album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_album_similar ADD CONSTRAINT FK_E4C840C19414124B FOREIGN KEY (album_target) REFERENCES indie_album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_album_artist ADD CONSTRAINT FK_1D5D4DBB1137ABCF FOREIGN KEY (album_id) REFERENCES indie_album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_album_artist ADD CONSTRAINT FK_1D5D4DBBB7970CF8 FOREIGN KEY (artist_id) REFERENCES indie_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_band_artist ADD CONSTRAINT FK_E61EBF2149ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_band_artist ADD CONSTRAINT FK_E61EBF21B7970CF8 FOREIGN KEY (artist_id) REFERENCES indie_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_band_image ADD CONSTRAINT FK_3A39A5513DA5256D FOREIGN KEY (image_id) REFERENCES aropixel_image (id)');
        $this->addSql('ALTER TABLE indie_band_image ADD CONSTRAINT FK_3A39A55149ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_band_image_crop ADD CONSTRAINT FK_C13242AA3DA5256D FOREIGN KEY (image_id) REFERENCES indie_band_image (id)');
        $this->addSql('ALTER TABLE indie_band_translation ADD CONSTRAINT FK_F2DE3183232D562B FOREIGN KEY (object_id) REFERENCES indie_band (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_release ADD CONSTRAINT FK_13571AC0EA9FDD75 FOREIGN KEY (media_id) REFERENCES indie_media (id)');
        $this->addSql('ALTER TABLE indie_release ADD CONSTRAINT FK_13571AC0BF396750 FOREIGN KEY (id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_track ADD CONSTRAINT FK_758C9071BF396750 FOREIGN KEY (id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_tracklist ADD CONSTRAINT FK_DD01D5E51137ABCF FOREIGN KEY (album_id) REFERENCES indie_album (id)');
        $this->addSql('ALTER TABLE indie_tracklist ADD CONSTRAINT FK_DD01D5E55ED23C43 FOREIGN KEY (track_id) REFERENCES indie_track (id)');
        $this->addSql('ALTER TABLE indie_tracklist_release ADD CONSTRAINT FK_62FD8DCA8C5F30E1 FOREIGN KEY (tracklist_id) REFERENCES indie_tracklist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE indie_tracklist_release ADD CONSTRAINT FK_62FD8DCAB12A727D FOREIGN KEY (release_id) REFERENCES indie_release (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F99395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F972F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F94D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES sylius_address (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F979D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES sylius_address (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sylius_order_item ADD CONSTRAINT FK_77B587ED8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order_item ADD CONSTRAINT FK_77B587ED3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_order_item_unit ADD CONSTRAINT FK_82BF226EE415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order_item_unit ADD CONSTRAINT FK_82BF226E7BE036FC FOREIGN KEY (shipment_id) REFERENCES sylius_shipment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sylius_payment ADD CONSTRAINT FK_D9191BD48D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_payment ADD CONSTRAINT FK_D9191BD419883967 FOREIGN KEY (method_id) REFERENCES sylius_payment_method (id)');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7449ABEB17 FOREIGN KEY (band_id) REFERENCES indie_band (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sylius_product_channels ADD CONSTRAINT FK_F9EF269B4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_channels ADD CONSTRAINT FK_F9EF269B72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_options ADD CONSTRAINT FK_2B5FF0094584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_options ADD CONSTRAINT FK_2B5FF009A7C41D6F FOREIGN KEY (option_id) REFERENCES sylius_product_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_option_translation ADD CONSTRAINT FK_CBA491AD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_product_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_option_value ADD CONSTRAINT FK_F7FF7D4BA7C41D6F FOREIGN KEY (option_id) REFERENCES sylius_product_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_option_value_translation ADD CONSTRAINT FK_8D4382DC2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_product_option_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_translation ADD CONSTRAINT FK_105A9082C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant ADD CONSTRAINT FK_A29B5234584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value ADD CONSTRAINT FK_76CDAFA13B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value ADD CONSTRAINT FK_76CDAFA1D957CA06 FOREIGN KEY (option_value_id) REFERENCES sylius_product_option_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_province ADD CONSTRAINT FK_B5618FE4F92F3E70 FOREIGN KEY (country_id) REFERENCES sylius_country (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B338D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B3319883967 FOREIGN KEY (method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE119F2C3FAB FOREIGN KEY (zone_id) REFERENCES sylius_zone (id)');
        $this->addSql('ALTER TABLE sylius_zone_member ADD CONSTRAINT FK_E8B5ABF39F2C3FAB FOREIGN KEY (zone_id) REFERENCES sylius_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aropixel_admin_user_image DROP FOREIGN KEY FK_9D7DD203DA5256D');
        $this->addSql('ALTER TABLE aropixel_admin_user_image DROP FOREIGN KEY FK_9D7DD20A76ED395');
        $this->addSql('ALTER TABLE indie_album DROP FOREIGN KEY FK_9AF70694BF396750');
        $this->addSql('ALTER TABLE indie_album_similar DROP FOREIGN KEY FK_E4C840C18DF142C4');
        $this->addSql('ALTER TABLE indie_album_similar DROP FOREIGN KEY FK_E4C840C19414124B');
        $this->addSql('ALTER TABLE indie_album_artist DROP FOREIGN KEY FK_1D5D4DBB1137ABCF');
        $this->addSql('ALTER TABLE indie_album_artist DROP FOREIGN KEY FK_1D5D4DBBB7970CF8');
        $this->addSql('ALTER TABLE indie_band_artist DROP FOREIGN KEY FK_E61EBF2149ABEB17');
        $this->addSql('ALTER TABLE indie_band_artist DROP FOREIGN KEY FK_E61EBF21B7970CF8');
        $this->addSql('ALTER TABLE indie_band_image DROP FOREIGN KEY FK_3A39A5513DA5256D');
        $this->addSql('ALTER TABLE indie_band_image DROP FOREIGN KEY FK_3A39A55149ABEB17');
        $this->addSql('ALTER TABLE indie_band_image_crop DROP FOREIGN KEY FK_C13242AA3DA5256D');
        $this->addSql('ALTER TABLE indie_band_translation DROP FOREIGN KEY FK_F2DE3183232D562B');
        $this->addSql('ALTER TABLE indie_release DROP FOREIGN KEY FK_13571AC0EA9FDD75');
        $this->addSql('ALTER TABLE indie_release DROP FOREIGN KEY FK_13571AC0BF396750');
        $this->addSql('ALTER TABLE indie_track DROP FOREIGN KEY FK_758C9071BF396750');
        $this->addSql('ALTER TABLE indie_tracklist DROP FOREIGN KEY FK_DD01D5E51137ABCF');
        $this->addSql('ALTER TABLE indie_tracklist DROP FOREIGN KEY FK_DD01D5E55ED23C43');
        $this->addSql('ALTER TABLE indie_tracklist_release DROP FOREIGN KEY FK_62FD8DCA8C5F30E1');
        $this->addSql('ALTER TABLE indie_tracklist_release DROP FOREIGN KEY FK_62FD8DCAB12A727D');
        $this->addSql('ALTER TABLE sylius_order DROP FOREIGN KEY FK_6196A1F99395C3F3');
        $this->addSql('ALTER TABLE sylius_order DROP FOREIGN KEY FK_6196A1F972F5A1AA');
        $this->addSql('ALTER TABLE sylius_order DROP FOREIGN KEY FK_6196A1F94D4CFF2B');
        $this->addSql('ALTER TABLE sylius_order DROP FOREIGN KEY FK_6196A1F979D0C0E4');
        $this->addSql('ALTER TABLE sylius_order_item DROP FOREIGN KEY FK_77B587ED8D9F6D38');
        $this->addSql('ALTER TABLE sylius_order_item DROP FOREIGN KEY FK_77B587ED3B69A9AF');
        $this->addSql('ALTER TABLE sylius_order_item_unit DROP FOREIGN KEY FK_82BF226EE415FB15');
        $this->addSql('ALTER TABLE sylius_order_item_unit DROP FOREIGN KEY FK_82BF226E7BE036FC');
        $this->addSql('ALTER TABLE sylius_payment DROP FOREIGN KEY FK_D9191BD48D9F6D38');
        $this->addSql('ALTER TABLE sylius_payment DROP FOREIGN KEY FK_D9191BD419883967');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B7449ABEB17');
        $this->addSql('ALTER TABLE sylius_product_channels DROP FOREIGN KEY FK_F9EF269B4584665A');
        $this->addSql('ALTER TABLE sylius_product_channels DROP FOREIGN KEY FK_F9EF269B72F5A1AA');
        $this->addSql('ALTER TABLE sylius_product_options DROP FOREIGN KEY FK_2B5FF0094584665A');
        $this->addSql('ALTER TABLE sylius_product_options DROP FOREIGN KEY FK_2B5FF009A7C41D6F');
        $this->addSql('ALTER TABLE sylius_product_option_translation DROP FOREIGN KEY FK_CBA491AD2C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product_option_value DROP FOREIGN KEY FK_F7FF7D4BA7C41D6F');
        $this->addSql('ALTER TABLE sylius_product_option_value_translation DROP FOREIGN KEY FK_8D4382DC2C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product_translation DROP FOREIGN KEY FK_105A9082C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product_variant DROP FOREIGN KEY FK_A29B5234584665A');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value DROP FOREIGN KEY FK_76CDAFA13B69A9AF');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value DROP FOREIGN KEY FK_76CDAFA1D957CA06');
        $this->addSql('ALTER TABLE sylius_province DROP FOREIGN KEY FK_B5618FE4F92F3E70');
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B338D9F6D38');
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B3319883967');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE119F2C3FAB');
        $this->addSql('ALTER TABLE sylius_zone_member DROP FOREIGN KEY FK_E8B5ABF39F2C3FAB');
        $this->addSql('DROP TABLE aropixel_admin_user');
        $this->addSql('DROP TABLE aropixel_admin_user_image');
        $this->addSql('DROP TABLE aropixel_file');
        $this->addSql('DROP TABLE aropixel_image');
        $this->addSql('DROP TABLE indie_album');
        $this->addSql('DROP TABLE indie_album_similar');
        $this->addSql('DROP TABLE indie_album_artist');
        $this->addSql('DROP TABLE indie_artist');
        $this->addSql('DROP TABLE indie_band');
        $this->addSql('DROP TABLE indie_band_artist');
        $this->addSql('DROP TABLE indie_band_image');
        $this->addSql('DROP TABLE indie_band_image_crop');
        $this->addSql('DROP TABLE indie_band_translation');
        $this->addSql('DROP TABLE indie_media');
        $this->addSql('DROP TABLE indie_release');
        $this->addSql('DROP TABLE indie_track');
        $this->addSql('DROP TABLE indie_tracklist');
        $this->addSql('DROP TABLE indie_tracklist_release');
        $this->addSql('DROP TABLE sylius_address');
        $this->addSql('DROP TABLE sylius_channel');
        $this->addSql('DROP TABLE sylius_country');
        $this->addSql('DROP TABLE sylius_customer');
        $this->addSql('DROP TABLE sylius_order');
        $this->addSql('DROP TABLE sylius_order_item');
        $this->addSql('DROP TABLE sylius_order_item_unit');
        $this->addSql('DROP TABLE sylius_payment');
        $this->addSql('DROP TABLE sylius_payment_method');
        $this->addSql('DROP TABLE sylius_product');
        $this->addSql('DROP TABLE sylius_product_channels');
        $this->addSql('DROP TABLE sylius_product_options');
        $this->addSql('DROP TABLE sylius_product_option');
        $this->addSql('DROP TABLE sylius_product_option_translation');
        $this->addSql('DROP TABLE sylius_product_option_value');
        $this->addSql('DROP TABLE sylius_product_option_value_translation');
        $this->addSql('DROP TABLE sylius_product_translation');
        $this->addSql('DROP TABLE sylius_product_variant');
        $this->addSql('DROP TABLE sylius_product_variant_option_value');
        $this->addSql('DROP TABLE sylius_promotion');
        $this->addSql('DROP TABLE sylius_province');
        $this->addSql('DROP TABLE sylius_shipment');
        $this->addSql('DROP TABLE sylius_shipping_method');
        $this->addSql('DROP TABLE sylius_zone');
        $this->addSql('DROP TABLE sylius_zone_member');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
