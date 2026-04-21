<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Some environments have a partial `animal` table (e.g. only id + user_id + soft-delete columns).
 * Doctrine expects nom, espece, race, poids, etatSante, etc. — add any that are missing.
 */
final class Version20260422120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure animal table has all columns required by App\\Entity\\Animal';
    }

    /**
     * @return list<string> lowercased column names
     */
    private function animalColumnNamesLower(): array
    {
        $db = (string) $this->connection->fetchOne('SELECT DATABASE()');
        $rows = $this->connection->fetchAllAssociative(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$db, 'animal'],
        );

        return array_map(static fn (array $r) => strtolower((string) $r['COLUMN_NAME']), $rows);
    }

    public function up(Schema $schema): void
    {
        $cols = $this->animalColumnNamesLower();

        if (!\in_array('nom', $cols, true)) {
            $this->addSql("ALTER TABLE animal ADD nom VARCHAR(255) NOT NULL DEFAULT 'Sans nom'");
        }
        if (!\in_array('espece', $cols, true)) {
            $this->addSql("ALTER TABLE animal ADD espece VARCHAR(255) NOT NULL DEFAULT 'Non renseigné'");
        }
        if (!\in_array('race', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD race VARCHAR(255) DEFAULT NULL');
        }
        if (!\in_array('poids', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD poids DOUBLE PRECISION DEFAULT NULL');
        }
        if (!\in_array('etatsante', $cols, true)) {
            $this->addSql("ALTER TABLE animal ADD etatSante VARCHAR(50) DEFAULT 'Sain'");
        }
        if (!\in_array('user_id', $cols, true) && !\in_array('userid', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD user_id INT DEFAULT NULL');
        }
        if (!\in_array('date_naissance', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD date_naissance DATE DEFAULT NULL');
        }
        if (!\in_array('deleted_at', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD deleted_at DATETIME DEFAULT NULL');
        }
        if (!\in_array('image_name', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD image_name VARCHAR(255) DEFAULT NULL');
        }
        if (!\in_array('external_image_url', $cols, true)) {
            $this->addSql('ALTER TABLE animal ADD external_image_url LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // Non-destructive rollback: only drop columns this migration may have added alone (unsafe to guess).
        $this->throwIrreversibleMigrationException();
    }
}
