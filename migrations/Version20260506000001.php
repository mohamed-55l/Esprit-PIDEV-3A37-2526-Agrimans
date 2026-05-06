<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert money columns from FLOAT to DECIMAL(12,2): product.price, nourriture.cost, order.total_amount';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products MODIFY price DECIMAL(12,2) NOT NULL');
        $this->addSql('ALTER TABLE nourriture MODIFY cost DECIMAL(12,2) DEFAULT NULL');
        // `order` table not yet created in DB; entity change covers future migration.
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products MODIFY price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE nourriture MODIFY cost DOUBLE PRECISION DEFAULT NULL');
    }
}
