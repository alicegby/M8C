<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224152513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE newsletter_subscriptions ALTER unsubscribe_token DROP NOT NULL');
        $this->addSql('ALTER TABLE users ADD newsletter BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE users ALTER COLUMN newsletter DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD is_verified BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE users ALTER COLUMN is_verified DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD email_verification_token VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ALTER dob DROP NOT NULL');
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
        $this->addSql('ALTER TABLE newsletter_subscriptions ALTER unsubscribe_token SET NOT NULL');
        $this->addSql('ALTER TABLE users DROP newsletter');
        $this->addSql('ALTER TABLE users DROP is_verified');
        $this->addSql('ALTER TABLE users DROP email_verification_token');
        $this->addSql('ALTER TABLE users ALTER dob SET NOT NULL');
    }
}
