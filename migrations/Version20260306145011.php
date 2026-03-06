<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306145011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table stats (JSONB) pour le stockage NoSQL des statistiques';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stats (
            id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            data JSONB NOT NULL,
            created_at TIMESTAMP DEFAULT NOW()
        )');

        $this->addSql('CREATE INDEX idx_stats_type ON stats(type)');
        $this->addSql('CREATE INDEX idx_stats_created_at ON stats(created_at)');
        $this->addSql('CREATE INDEX idx_stats_data ON stats USING gin(data)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stats');
    }
}