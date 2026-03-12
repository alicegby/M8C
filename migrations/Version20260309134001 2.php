<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309134001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bloc_notes ADD game_session_id UUID NOT NULL');
        $this->addSql('ALTER TABLE bloc_notes ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE bloc_notes ADD CONSTRAINT FK_5BBC1578FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_5BBC1578FE32B32 ON bloc_notes (game_session_id)');
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
        $this->addSql('ALTER TABLE bloc_notes DROP CONSTRAINT FK_5BBC1578FE32B32');
        $this->addSql('DROP INDEX IDX_5BBC1578FE32B32');
        $this->addSql('ALTER TABLE bloc_notes DROP game_session_id');
        $this->addSql('ALTER TABLE bloc_notes ALTER created_at SET DEFAULT \'2026-03-09 13:33:29.125195\'');
    }
}
