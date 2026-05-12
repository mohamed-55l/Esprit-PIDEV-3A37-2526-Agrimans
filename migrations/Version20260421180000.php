<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the users table expected by App\Entity\User (security provider).
 * Legacy migration Version20260407231419 is skipped on many DBs, so users was never created.
 */
final class Version20260421180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table + optional demo account if empty';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(180) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Demo login only when table is still empty (password: agrimans123 — change in production)
        $this->addSql('INSERT INTO users (full_name, email, phone, password_hash, role, created_at)
            SELECT v.full_name, v.email, v.phone, v.password_hash, v.role, v.created_at
            FROM (
                SELECT
                    \'Compte démo\' AS full_name,
                    \'demo@agrimans.local\' AS email,
                    NULL AS phone,
                    \'$2y$13$oK8.DT/pNNHTpBLYwV21tevJ94rwDlY1bsfF6RCnrH/nbgqXssXUe\' AS password_hash,
                    \'USER\' AS role,
                    NOW() AS created_at
            ) AS v
            WHERE NOT EXISTS (SELECT 1 FROM users LIMIT 1)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM users WHERE email = \'demo@agrimans.local\'');
        $this->addSql('DROP TABLE users');
    }
}
