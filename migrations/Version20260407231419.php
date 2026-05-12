<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407231419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // One-shot legacy migration from an older schema. Skip when the DB was already migrated
        // (e.g. no fk_animal_user on animal) to avoid errors on DROP FOREIGN KEY / CREATE duplicates.
        $sm = $this->connection->createSchemaManager();
        if ($sm->tablesExist(['animal'])) {
            $fks = $sm->introspectTable('animal')->getForeignKeys();
            $this->skipIf(
                !isset($fks['fk_animal_user']),
                'Base déjà alignée : migration historique Version20260407231419 ignorée (fk_animal_user absente).',
            );
        }

        // Table may already exist if Messenger auto_setup ran or a previous deploy created it.
        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY fk_animal_user');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY cart_item_ibfk_1');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY cart_item_ibfk_2');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY demande_ibfk_2');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY fk_demande_user_new');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY order_ibfk_1');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY order_item_ibfk_1');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY order_item_ibfk_2');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY product_ibfk_1');
        $this->addSql('ALTER TABLE user_otp DROP FOREIGN KEY user_otp_ibfk_1');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE email_otp');
        $this->addSql('DROP TABLE garage');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_otp');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY fk_cart_items_cart_id');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY fk_cart_items_product_id');
        $this->addSql('ALTER TABLE cart_items DROP added_at');
        $this->addSql('DROP INDEX cart_id ON cart_items');
        $this->addSql('CREATE INDEX IDX_BEF484451AD5CDBF ON cart_items (cart_id)');
        $this->addSql('DROP INDEX product_id ON cart_items');
        $this->addSql('CREATE INDEX IDX_BEF484454584665A ON cart_items (product_id)');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT fk_cart_items_cart_id FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT fk_cart_items_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY fk_carts_buyer_id');
        $this->addSql('DROP INDEX buyer_id ON carts');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY fk_culture_parcelle');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY fk_culture_parcelle');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type_culture type_culture VARCHAR(255) DEFAULT NULL, CHANGE etat_culture etat_culture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT FK_B6A99CEB4433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle)');
        $this->addSql('DROP INDEX fk_culture_parcelle ON culture');
        $this->addSql('CREATE INDEX IDX_B6A99CEB4433ED66 ON culture (parcelle_id)');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT fk_culture_parcelle FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY fk_equipement_user');
        $this->addSql('DROP INDEX fk_equipement_user ON equipement');
        $this->addSql('ALTER TABLE equipement CHANGE prix prix DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_geo DROP FOREIGN KEY equipement_geo_ibfk_2');
        $this->addSql('DROP INDEX garage_id ON equipement_geo');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY fk_parcelle_user');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY fk_parcelle_user');
        $this->addSql('ALTER TABLE parcelle CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type_sol type_sol VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX fk_parcelle_user ON parcelle');
        $this->addSql('CREATE INDEX IDX_C56E2CF6FB88E14F ON parcelle (utilisateur_id)');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT fk_parcelle_user FOREIGN KEY (utilisateur_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY fk_products_seller_id');
        $this->addSql('DROP INDEX seller_id ON products');
        $this->addSql('ALTER TABLE products CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY fk_ratings_product_id');
        $this->addSql('ALTER TABLE ratings CHANGE comment comment LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX product_id ON ratings');
        $this->addSql('CREATE INDEX IDX_CEB607C94584665A ON ratings (product_id)');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT fk_ratings_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_users_new');
        $this->addSql('DROP INDEX fk_review_users_new ON review');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY review_ibfk_1');
        $this->addSql('ALTER TABLE review CHANGE commentaire commentaire LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX equipement_id ON review');
        $this->addSql('CREATE INDEX IDX_794381C6806F0F5C ON review (equipement_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT review_ibfk_1 FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP password, DROP role, DROP ferme_id, CHANGE email email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, espece VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, race VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, poids FLOAT DEFAULT NULL, etatSante VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'Sain\' COLLATE `utf8mb4_general_ci`, userId INT DEFAULT NULL, INDEX fk_animal_user (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, quantity FLOAT DEFAULT \'1\' NOT NULL, added_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX product_id (product_id), INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, agriculteur_id INT NOT NULL, equipement_id INT NOT NULL, nom_equipement VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_demande VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, quantite INT DEFAULT 1, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, commentaire TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_demande DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statut VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'EN_ATTENTE\' COLLATE `utf8mb4_general_ci`, reponse_chef TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_traitement DATETIME DEFAULT NULL, INDEX equipement_id (equipement_id), INDEX fk_demande_user_new (agriculteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE email_otp (email VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, expiry DATETIME NOT NULL, PRIMARY KEY(email)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE garage (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, adresse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, capacite INT DEFAULT 10, responsable VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, telephone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total_amount FLOAT NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'PENDING\' COLLATE `utf8mb4_general_ci`, order_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, product_id INT NOT NULL, quantity FLOAT NOT NULL, price_at_purchase FLOAT NOT NULL, INDEX order_id (order_id), INDEX product_id (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, price FLOAT NOT NULL, quantity FLOAT NOT NULL, category_name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, supplier VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, image_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'­ƒìÄ\' COLLATE `utf8mb4_general_ci`, expiry_date DATE DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, password_hash VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role ENUM(\'ADMIN\', \'USER\') CHARACTER SET utf8mb4 DEFAULT \'USER\' NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_otp (user_id INT NOT NULL, otp_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, expires_at DATETIME NOT NULL, attempts INT DEFAULT 0, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT fk_animal_user FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT cart_item_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT cart_item_ibfk_2 FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT demande_ibfk_2 FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT fk_demande_user_new FOREIGN KEY (agriculteur_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT order_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT order_item_ibfk_1 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT order_item_ibfk_2 FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT product_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_otp ADD CONSTRAINT user_otp_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT fk_carts_buyer_id FOREIGN KEY (buyer_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX buyer_id ON carts (buyer_id)');
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
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE type_culture type_culture VARCHAR(100) DEFAULT NULL, CHANGE etat_culture etat_culture VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT fk_culture_parcelle FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_b6a99ceb4433ed66 ON culture');
        $this->addSql('CREATE INDEX fk_culture_parcelle ON culture (parcelle_id)');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT FK_B6A99CEB4433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id_parcelle)');
        $this->addSql('ALTER TABLE equipement CHANGE prix prix FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT fk_equipement_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX fk_equipement_user ON equipement (user_id)');
        $this->addSql('ALTER TABLE equipement_geo ADD CONSTRAINT equipement_geo_ibfk_2 FOREIGN KEY (garage_id) REFERENCES garage (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX garage_id ON equipement_geo (garage_id)');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF6FB88E14F');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF6FB88E14F');
        $this->addSql('ALTER TABLE parcelle CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE type_sol type_sol VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT fk_parcelle_user FOREIGN KEY (utilisateur_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_c56e2cf6fb88e14f ON parcelle');
        $this->addSql('CREATE INDEX fk_parcelle_user ON parcelle (utilisateur_id)');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE products CHANGE price price FLOAT NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_seller_id FOREIGN KEY (seller_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX seller_id ON products (seller_id)');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C94584665A');
        $this->addSql('ALTER TABLE ratings CHANGE comment comment TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_ceb607c94584665a ON ratings');
        $this->addSql('CREATE INDEX product_id ON ratings (product_id)');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C94584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6806F0F5C');
        $this->addSql('ALTER TABLE review CHANGE commentaire commentaire TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_users_new FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_review_users_new ON review (user_id)');
        $this->addSql('DROP INDEX idx_794381c6806f0f5c ON review');
        $this->addSql('CREATE INDEX equipement_id ON review (equipement_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(100) DEFAULT NULL, ADD prenom VARCHAR(100) DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL, ADD role ENUM(\'ADMIN\', \'USER\') DEFAULT \'USER\', ADD ferme_id INT DEFAULT NULL, CHANGE email email VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email)');
    }
}
