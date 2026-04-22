<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422010003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE garage_equipement (garage_id INT NOT NULL, equipement_id INT NOT NULL, INDEX IDX_C1B083F5C4FFF555 (garage_id), INDEX IDX_C1B083F5806F0F5C (equipement_id), PRIMARY KEY(garage_id, equipement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE garage_equipement ADD CONSTRAINT FK_C1B083F5C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id)');
        $this->addSql('ALTER TABLE garage_equipement ADD CONSTRAINT FK_C1B083F5806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id)');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX fk_animal_user ON animal');
        $this->addSql('ALTER TABLE animal CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE espece espece VARCHAR(255) NOT NULL, CHANGE race race VARCHAR(255) DEFAULT NULL, CHANGE poids poids DOUBLE PRECISION DEFAULT NULL, CHANGE etatSante etatSante VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE animal_nourriture CHANGE quantity_fed quantity_fed DOUBLE PRECISION NOT NULL, CHANGE notes notes VARCHAR(255) DEFAULT NULL, CHANGE animal_id animal_id INT DEFAULT NULL, CHANGE nourriture_id nourriture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484454584665A');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY fk_cart_items_cart_id');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY fk_cart_items_product_id');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484454584665A');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY fk_cart_items_cart_id');
        $this->addSql('ALTER TABLE cart_items DROP added_at');
        $this->addSql('DROP INDEX cart_id ON cart_items');
        $this->addSql('CREATE INDEX IDX_BEF484451AD5CDBF ON cart_items (cart_id)');
        $this->addSql('DROP INDEX product_id ON cart_items');
        $this->addSql('CREATE INDEX IDX_BEF484454584665A ON cart_items (product_id)');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT fk_cart_items_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484454584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT fk_cart_items_cart_id FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY fk_carts_buyer_id');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY fk_carts_buyer_id');
        $this->addSql('ALTER TABLE carts CHANGE buyer_id buyer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AAC6C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX buyer_id ON carts');
        $this->addSql('CREATE INDEX IDX_4E004AAC6C755722 ON carts (buyer_id)');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT fk_carts_buyer_id FOREIGN KEY (buyer_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE culture ADD info_file_name VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type_culture type_culture VARCHAR(255) DEFAULT NULL, CHANGE etat_culture etat_culture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT FK_B6A99CEB4433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle)');
        $this->addSql('DROP INDEX fk_culture_parcelle ON culture');
        $this->addSql('CREATE INDEX IDX_B6A99CEB4433ED66 ON culture (parcelle_id)');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY fk_equipement_user');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY fk_equipement_user');
        $this->addSql('ALTER TABLE equipement CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, CHANGE disponibilite disponibilite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX fk_equipement_user ON equipement');
        $this->addSql('CREATE INDEX IDX_B8B4C6F3A76ED395 ON equipement (user_id)');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT fk_equipement_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE equipement_geo DROP FOREIGN KEY equipement_geo_ibfk_2');
        $this->addSql('ALTER TABLE equipement_geo DROP FOREIGN KEY FK_7ED7FDDF806F0F5C');
        $this->addSql('DROP INDEX garage_id ON equipement_geo');
        $this->addSql('ALTER TABLE garage CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE capacite capacite INT DEFAULT NULL, CHANGE responsable responsable VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE queue_name queue_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE nourriture CHANGE type type VARCHAR(255) NOT NULL, CHANGE quantity quantity DOUBLE PRECISION NOT NULL, CHANGE unit unit VARCHAR(255) DEFAULT NULL, CHANGE cost cost DOUBLE PRECISION DEFAULT NULL, CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX user_id ON `order`');
        $this->addSql('ALTER TABLE `order` CHANGE total_amount total_amount DOUBLE PRECISION NOT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE order_date order_date DATETIME NOT NULL');
        $this->addSql('DROP INDEX product_id ON order_item');
        $this->addSql('DROP INDEX order_id ON order_item');
        $this->addSql('ALTER TABLE order_item CHANGE quantity quantity DOUBLE PRECISION NOT NULL, CHANGE price_at_purchase price_at_purchase DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE parcelle CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type_sol type_sol VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX fk_parcelle_user ON parcelle');
        $this->addSql('CREATE INDEX IDX_C56E2CF6FB88E14F ON parcelle (utilisateur_id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY fk_products_seller_id');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY fk_products_seller_id');
        $this->addSql('ALTER TABLE products CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE category category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A8DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX seller_id ON products');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A8DE820D9 ON products (seller_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_seller_id FOREIGN KEY (seller_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY fk_ratings_product_id');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C94584665A');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C94584665A');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY fk_ratings_product_id');
        $this->addSql('ALTER TABLE ratings CHANGE product_id product_id INT DEFAULT NULL, CHANGE comment comment LONGTEXT DEFAULT NULL, CHANGE price_category price_category VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C94584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('DROP INDEX product_id ON ratings');
        $this->addSql('CREATE INDEX IDX_CEB607C94584665A ON ratings (product_id)');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C94584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT fk_ratings_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_users_new');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY review_ibfk_1');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6806F0F5C');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_users_new');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY review_ibfk_1');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6806F0F5C');
        $this->addSql('ALTER TABLE review CHANGE commentaire commentaire LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id)');
        $this->addSql('DROP INDEX fk_794381c6806f0f5c ON review');
        $this->addSql('CREATE INDEX IDX_794381C6806F0F5C ON review (equipement_id)');
        $this->addSql('DROP INDEX fk_review_users_new ON review');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_users_new FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT review_ibfk_1 FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX UNIQ_825FFC90E7927C74 ON user_auth');
        $this->addSql('ALTER TABLE user_auth CHANGE email email VARCHAR(255) NOT NULL, CHANGE roles roles LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE user_otp CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE attempts attempts INT DEFAULT NULL');
        $this->addSql('DROP INDEX email ON users');
        $this->addSql('ALTER TABLE users CHANGE full_name full_name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, price FLOAT NOT NULL, quantity FLOAT NOT NULL, category_name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, supplier VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, image_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'­ƒìÄ\' COLLATE `utf8mb4_general_ci`, expiry_date DATE DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, role ENUM(\'ADMIN\', \'USER\') CHARACTER SET utf8mb4 DEFAULT \'USER\' COLLATE `utf8mb4_general_ci`, ferme_id INT DEFAULT NULL, UNIQUE INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE garage_equipement DROP FOREIGN KEY FK_C1B083F5C4FFF555');
        $this->addSql('ALTER TABLE garage_equipement DROP FOREIGN KEY FK_C1B083F5806F0F5C');
        $this->addSql('DROP TABLE garage_equipement');
        $this->addSql('ALTER TABLE animal CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE espece espece VARCHAR(100) NOT NULL, CHANGE race race VARCHAR(100) DEFAULT NULL, CHANGE poids poids FLOAT DEFAULT NULL, CHANGE etatSante etatSante VARCHAR(50) DEFAULT \'Sain\'');
        $this->addSql('CREATE INDEX fk_animal_user ON animal (userId)');
        $this->addSql('ALTER TABLE animal_nourriture CHANGE quantity_fed quantity_fed NUMERIC(10, 2) NOT NULL, CHANGE notes notes VARCHAR(500) DEFAULT NULL, CHANGE animal_id animal_id INT NOT NULL, CHANGE nourriture_id nourriture_id INT NOT NULL');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AAC6C755722');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AAC6C755722');
        $this->addSql('ALTER TABLE carts CHANGE buyer_id buyer_id INT NOT NULL');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT fk_carts_buyer_id FOREIGN KEY (buyer_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_4e004aac6c755722 ON carts');
        $this->addSql('CREATE INDEX buyer_id ON carts (buyer_id)');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AAC6C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484454584665A');
        $this->addSql('ALTER TABLE cart_items ADD added_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_bef484454584665a ON cart_items');
        $this->addSql('CREATE INDEX product_id ON cart_items (product_id)');
        $this->addSql('DROP INDEX idx_bef484451ad5cdbf ON cart_items');
        $this->addSql('CREATE INDEX cart_id ON cart_items (cart_id)');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484454584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY FK_B6A99CEB4433ED66');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY FK_B6A99CEB4433ED66');
        $this->addSql('ALTER TABLE culture DROP info_file_name, DROP updated_at, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE type_culture type_culture VARCHAR(100) DEFAULT NULL, CHANGE etat_culture etat_culture VARCHAR(100) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_b6a99ceb4433ed66 ON culture');
        $this->addSql('CREATE INDEX fk_culture_parcelle ON culture (parcelle_id)');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT FK_B6A99CEB4433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle)');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3A76ED395');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3A76ED395');
        $this->addSql('ALTER TABLE equipement CHANGE nom nom VARCHAR(100) DEFAULT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL, CHANGE prix prix FLOAT DEFAULT NULL, CHANGE disponibilite disponibilite VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT fk_equipement_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_b8b4c6f3a76ed395 ON equipement');
        $this->addSql('CREATE INDEX fk_equipement_user ON equipement (user_id)');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE equipement_geo ADD CONSTRAINT equipement_geo_ibfk_2 FOREIGN KEY (garage_id) REFERENCES garage (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX garage_id ON equipement_geo (garage_id)');
        $this->addSql('ALTER TABLE garage CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE capacite capacite INT DEFAULT 10, CHANGE responsable responsable VARCHAR(100) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE queue_name queue_name VARCHAR(190) NOT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE nourriture CHANGE type type VARCHAR(100) NOT NULL, CHANGE quantity quantity NUMERIC(10, 2) NOT NULL, CHANGE unit unit VARCHAR(50) DEFAULT NULL, CHANGE cost cost NUMERIC(10, 2) DEFAULT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE total_amount total_amount FLOAT NOT NULL, CHANGE status status VARCHAR(50) DEFAULT \'PENDING\', CHANGE order_date order_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE INDEX user_id ON `order` (user_id)');
        $this->addSql('ALTER TABLE order_item CHANGE quantity quantity FLOAT NOT NULL, CHANGE price_at_purchase price_at_purchase FLOAT NOT NULL');
        $this->addSql('CREATE INDEX product_id ON order_item (product_id)');
        $this->addSql('CREATE INDEX order_id ON order_item (order_id)');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF6FB88E14F');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF6FB88E14F');
        $this->addSql('ALTER TABLE parcelle CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE type_sol type_sol VARCHAR(100) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_c56e2cf6fb88e14f ON parcelle');
        $this->addSql('CREATE INDEX fk_parcelle_user ON parcelle (utilisateur_id)');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A8DE820D9');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A8DE820D9');
        $this->addSql('ALTER TABLE products CHANGE price price FLOAT NOT NULL, CHANGE category category VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_seller_id FOREIGN KEY (seller_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_b3ba5a5a8de820d9 ON products');
        $this->addSql('CREATE INDEX seller_id ON products (seller_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A8DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C94584665A');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C94584665A');
        $this->addSql('ALTER TABLE ratings CHANGE comment comment TEXT DEFAULT NULL, CHANGE price_category price_category VARCHAR(20) DEFAULT NULL, CHANGE product_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT fk_ratings_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C94584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_ceb607c94584665a ON ratings');
        $this->addSql('CREATE INDEX product_id ON ratings (product_id)');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C94584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6806F0F5C');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6806F0F5C');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review CHANGE commentaire commentaire TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_users_new FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT review_ibfk_1 FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_794381c6a76ed395 ON review');
        $this->addSql('CREATE INDEX fk_review_users_new ON review (user_id)');
        $this->addSql('DROP INDEX idx_794381c6806f0f5c ON review');
        $this->addSql('CREATE INDEX FK_794381C6806F0F5C ON review (equipement_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users CHANGE full_name full_name VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(150) NOT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE role role ENUM(\'ADMIN\', \'USER\') DEFAULT \'USER\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX email ON users (email)');
        $this->addSql('ALTER TABLE user_auth CHANGE email email VARCHAR(180) NOT NULL, CHANGE roles roles JSON NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_825FFC90E7927C74 ON user_auth (email)');
        $this->addSql('ALTER TABLE user_otp CHANGE user_id user_id INT NOT NULL, CHANGE attempts attempts INT DEFAULT 0');
    }
}
