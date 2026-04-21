<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ensures animal.user_id exists (ORM maps property userId -> column user_id).
 * Fixes "Unknown column 'a0_.userId'" when the legacy table had no column or used camelCase userId.
 */
final class Version20260422100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align animal owner column: user_id (rename userId if present, else add)';
    }

    public function up(Schema $schema): void
    {
        $db = (string) $this->connection->fetchOne('SELECT DATABASE()');
        $rows = $this->connection->fetchAllAssociative(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$db, 'animal'],
        );
        $cols = array_map(static fn (array $r) => $r['COLUMN_NAME'], $rows);

        if (\in_array('user_id', $cols, true)) {
            return;
        }

        $camel = null;
        foreach ($cols as $c) {
            if (strcasecmp((string) $c, 'userId') === 0) {
                $camel = $c;
                break;
            }
        }

        if ($camel !== null) {
            $this->addSql('ALTER TABLE animal CHANGE `'.$camel.'` user_id INT DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE animal ADD user_id INT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $db = (string) $this->connection->fetchOne('SELECT DATABASE()');
        $rows = $this->connection->fetchAllAssociative(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$db, 'animal'],
        );
        $cols = array_map(static fn (array $r) => $r['COLUMN_NAME'], $rows);
        if (!\in_array('user_id', $cols, true)) {
            return;
        }
        $this->addSql('ALTER TABLE animal CHANGE user_id userId INT DEFAULT NULL');
    }
}
