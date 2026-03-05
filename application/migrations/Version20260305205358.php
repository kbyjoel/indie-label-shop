<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305205358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_product_options (product_id INT NOT NULL, option_id INT NOT NULL, INDEX IDX_2B5FF0094584665A (product_id), INDEX IDX_2B5FF009A7C41D6F (option_id), PRIMARY KEY (product_id, option_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_translation (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_value (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_option_value_translation (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sylius_product_variant_option_value (variant_id INT NOT NULL, option_value_id INT NOT NULL, INDEX IDX_76CDAFA13B69A9AF (variant_id), INDEX IDX_76CDAFA1D957CA06 (option_value_id), PRIMARY KEY (variant_id, option_value_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sylius_product_options ADD CONSTRAINT FK_2B5FF0094584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_options ADD CONSTRAINT FK_2B5FF009A7C41D6F FOREIGN KEY (option_id) REFERENCES sylius_product_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value ADD CONSTRAINT FK_76CDAFA13B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value ADD CONSTRAINT FK_76CDAFA1D957CA06 FOREIGN KEY (option_value_id) REFERENCES sylius_product_option_value (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_options DROP FOREIGN KEY FK_2B5FF0094584665A');
        $this->addSql('ALTER TABLE sylius_product_options DROP FOREIGN KEY FK_2B5FF009A7C41D6F');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value DROP FOREIGN KEY FK_76CDAFA13B69A9AF');
        $this->addSql('ALTER TABLE sylius_product_variant_option_value DROP FOREIGN KEY FK_76CDAFA1D957CA06');
        $this->addSql('DROP TABLE sylius_product_options');
        $this->addSql('DROP TABLE sylius_product_option');
        $this->addSql('DROP TABLE sylius_product_option_translation');
        $this->addSql('DROP TABLE sylius_product_option_value');
        $this->addSql('DROP TABLE sylius_product_option_value_translation');
        $this->addSql('DROP TABLE sylius_product_variant_option_value');
    }
}
