<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309131736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bloc_notes DROP CONSTRAINT fk_session');
        $this->addSql('ALTER TABLE bloc_notes DROP CONSTRAINT fk_player');
        $this->addSql('DROP TABLE bloc_notes');
        $this->addSql('ALTER TABLE game_players ALTER id DROP DEFAULT');
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
        $this->addSql('CREATE TABLE bloc_notes (id UUID DEFAULT \'gen_random_uuid()\' NOT NULL, player_id UUID NOT NULL, session_id UUID NOT NULL, content TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT \'now()\', updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT \'now()\', PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5BBC157613FECDF ON bloc_notes (session_id)');
        $this->addSql('CREATE INDEX IDX_5BBC15799E6F5DF ON bloc_notes (player_id)');
        $this->addSql('ALTER TABLE bloc_notes ADD CONSTRAINT fk_session FOREIGN KEY (session_id) REFERENCES game_sessions (id) ON UPDATE CASCADE ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bloc_notes ADD CONSTRAINT fk_player FOREIGN KEY (player_id) REFERENCES game_players (id) ON UPDATE CASCADE ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_players ALTER id SET DEFAULT \'gen_random_uuid()\'');
    }
}
