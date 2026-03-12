<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309101753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE stats');
        $this->addSql('ALTER TABLE game_players ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE game_players ALTER is_host SET DEFAULT false');
        $this->addSql('ALTER TABLE game_players ALTER is_ready SET DEFAULT false');
        $this->addSql('ALTER TABLE game_players ALTER user_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA realtime');
        $this->addSql('CREATE SCHEMA pgbouncer');
        $this->addSql('CREATE SCHEMA extensions');
        $this->addSql('CREATE SCHEMA vault');
        $this->addSql('CREATE SCHEMA graphql_public');
        $this->addSql('CREATE SCHEMA graphql');
        $this->addSql('CREATE SCHEMA auth');
        $this->addSql('CREATE SCHEMA storage');
        $this->addSql('CREATE TABLE stats (id UUID DEFAULT \'gen_random_uuid()\' NOT NULL, type VARCHAR(50) NOT NULL, data JSONB NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'now()\', PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_stats_data ON stats (data)');
        $this->addSql('CREATE INDEX idx_stats_type ON stats (type)');
        $this->addSql('CREATE INDEX idx_stats_created_at ON stats (created_at)');
        $this->addSql('ALTER TABLE game_players ALTER id SET DEFAULT \'gen_random_uuid()\'');
        $this->addSql('ALTER TABLE game_players ALTER is_host DROP DEFAULT');
        $this->addSql('ALTER TABLE game_players ALTER is_ready DROP DEFAULT');
        $this->addSql('ALTER TABLE game_players ALTER user_id SET NOT NULL');
    }
}
