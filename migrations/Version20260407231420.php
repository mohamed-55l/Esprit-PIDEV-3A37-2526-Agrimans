<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407231420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Create ProductBundle entity tables";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE product_bundles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT, original_price DOUBLE PRECISION NOT NULL, bundle_price DOUBLE PRECISION NOT NULL, discount_percentage DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE bundle_products (bundle_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(bundle_id, product_id), FOREIGN KEY(bundle_id) REFERENCES product_bundles(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS bundle_products");
        $this->addSql("DROP TABLE IF EXISTS product_bundles");
    }
}
