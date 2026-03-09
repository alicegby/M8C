<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309132448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_players DROP CONSTRAINT fk_b38c3c891136be75');
        $this->addSql('ALTER TABLE game_players ADD CONSTRAINT FK_B38C3C891136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE SET NULL NOT DEFERRABLE');
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
        $this->addSql('ALTER TABLE game_players DROP CONSTRAINT FK_B38C3C891136BE75');
        $this->addSql('ALTER TABLE game_players ADD CONSTRAINT fk_b38c3c891136be75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
