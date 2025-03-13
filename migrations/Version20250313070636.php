<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313070636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_details (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_details_product (order_details_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_FE10BEE08C0FA77 (order_details_id), INDEX IDX_FE10BEE04584665A (product_id), PRIMARY KEY(order_details_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_details_product ADD CONSTRAINT FK_FE10BEE08C0FA77 FOREIGN KEY (order_details_id) REFERENCES order_details (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_details_product ADD CONSTRAINT FK_FE10BEE04584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders ADD order_details_id INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE8C0FA77 FOREIGN KEY (order_details_id) REFERENCES order_details (id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE8C0FA77 ON orders (order_details_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE8C0FA77');
        $this->addSql('ALTER TABLE order_details_product DROP FOREIGN KEY FK_FE10BEE08C0FA77');
        $this->addSql('ALTER TABLE order_details_product DROP FOREIGN KEY FK_FE10BEE04584665A');
        $this->addSql('DROP TABLE order_details');
        $this->addSql('DROP TABLE order_details_product');
        $this->addSql('DROP INDEX IDX_E52FFDEE8C0FA77 ON orders');
        $this->addSql('ALTER TABLE orders DROP order_details_id');
    }
}
