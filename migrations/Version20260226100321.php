<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226100321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo_code_usages (id UUID NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, promo_code_id UUID NOT NULL, purchase_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E0BAA689A76ED395 ON promo_code_usages (user_id)');
        $this->addSql('CREATE INDEX IDX_E0BAA6892FAE4625 ON promo_code_usages (promo_code_id)');
        $this->addSql('CREATE INDEX IDX_E0BAA689558FBEB9 ON promo_code_usages (purchase_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_promo ON promo_code_usages (user_id, promo_code_id)');
        $this->addSql('ALTER TABLE promo_code_usages ADD CONSTRAINT FK_E0BAA689A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE promo_code_usages ADD CONSTRAINT FK_E0BAA6892FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE promo_code_usages ADD CONSTRAINT FK_E0BAA689558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchases (id) NOT DEFERRABLE');
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
        $this->addSql('ALTER TABLE promo_code_usages DROP CONSTRAINT FK_E0BAA689A76ED395');
        $this->addSql('ALTER TABLE promo_code_usages DROP CONSTRAINT FK_E0BAA6892FAE4625');
        $this->addSql('ALTER TABLE promo_code_usages DROP CONSTRAINT FK_E0BAA689558FBEB9');
        $this->addSql('DROP TABLE promo_code_usages');
    }
}
