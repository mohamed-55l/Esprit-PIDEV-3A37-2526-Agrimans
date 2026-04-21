<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Animal module: soft delete, photo, history, notifications, birth date';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE animal_history (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(32) NOT NULL, animal_id INT DEFAULT NULL, snapshot JSON DEFAULT NULL, detail LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_animal_history_created (created_at), INDEX idx_animal_history_animal (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, link VARCHAR(512) DEFAULT NULL, context VARCHAR(32) NOT NULL, read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_user_notification_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE animal ADD date_naissance DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD image_name VARCHAR(255) DEFAULT NULL, ADD external_image_url LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE animal DROP date_naissance, DROP deleted_at, DROP image_name, DROP external_image_url');
        $this->addSql('DROP TABLE user_notification');
        $this->addSql('DROP TABLE animal_history');
    }
}
