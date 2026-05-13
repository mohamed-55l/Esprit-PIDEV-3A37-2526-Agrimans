<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260513011749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_history (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(32) NOT NULL, animal_id INT DEFAULT NULL, snapshot JSON DEFAULT NULL, detail LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX idx_animal_history_created (created_at), INDEX idx_animal_history_animal (animal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, link VARCHAR(512) DEFAULT NULL, context VARCHAR(32) DEFAULT \'animal\' NOT NULL, read_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX idx_user_notification_user (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE equipement DROP capacite_rendement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE animal_history');
        $this->addSql('DROP TABLE user_notification');
        $this->addSql('ALTER TABLE equipement ADD capacite_rendement DOUBLE PRECISION DEFAULT NULL');
    }
}
