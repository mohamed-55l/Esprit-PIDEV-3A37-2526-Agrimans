<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260513002255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY `demande_ibfk_2`');
        $this->addSql('DROP TABLE animal_history');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_auth');
        $this->addSql('DROP TABLE user_notification');
        $this->addSql('ALTER TABLE animal ADD espece VARCHAR(255) NOT NULL, ADD etatSante VARCHAR(255) DEFAULT NULL, DROP type, DROP breed, DROP health_status, DROP deleted_at, DROP external_image_url, CHANGE name nom VARCHAR(255) NOT NULL, CHANGE image_name race VARCHAR(255) DEFAULT NULL, CHANGE weight poids DOUBLE PRECISION DEFAULT NULL, CHANGE user_id userId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement ADD capacite_rendement DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_history (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, animal_id INT DEFAULT NULL, snapshot JSON DEFAULT NULL, detail LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX idx_animal_history_animal (animal_id), INDEX idx_animal_history_created (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, agriculteur_id INT DEFAULT NULL, equipement_id INT DEFAULT NULL, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_demande DATETIME DEFAULT NULL, quantite INT DEFAULT NULL, nom_equipement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_demande VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, reponse_chef TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_traitement DATETIME DEFAULT NULL, INDEX agriculteur_id (agriculteur_id), INDEX equipement_id (equipement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, full_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, password_hash VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, role VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_auth (id INT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, roles JSON NOT NULL, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX UNIQ_825FFC90E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, link VARCHAR(512) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, context VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT \'animal\' NOT NULL COLLATE `utf8mb4_unicode_ci`, read_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX idx_user_notification_user (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT `demande_ibfk_2` FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal ADD name VARCHAR(255) NOT NULL, ADD type VARCHAR(100) NOT NULL, ADD breed VARCHAR(100) DEFAULT NULL, ADD health_status VARCHAR(50) DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD image_name VARCHAR(255) DEFAULT NULL, ADD external_image_url LONGTEXT DEFAULT NULL, DROP nom, DROP espece, DROP race, DROP etatSante, CHANGE poids weight DOUBLE PRECISION DEFAULT NULL, CHANGE userId user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement DROP capacite_rendement');
    }
}
