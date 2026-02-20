<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220143247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema — toutes les tables M8C';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE characters (id UUID NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) DEFAULT NULL, age INT DEFAULT NULL, job VARCHAR(100) DEFAULT NULL, histoire TEXT NOT NULL, mobile TEXT NOT NULL, alibi TEXT NOT NULL, extra_info TEXT DEFAULT NULL, is_guilty BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, murder_party_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3A29410ECC1959D0 ON characters (murder_party_id)');
        $this->addSql('CREATE TABLE clues (id UUID NOT NULL, content TEXT NOT NULL, trigger_minutes INT NOT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, murder_party_id UUID NOT NULL, character_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_EAFCA7E4CC1959D0 ON clues (murder_party_id)');
        $this->addSql('CREATE INDEX IDX_EAFCA7E41136BE75 ON clues (character_id)');
        $this->addSql('CREATE TABLE contact_messages (id UUID NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, subject VARCHAR(50) NOT NULL, message TEXT NOT NULL, is_read BOOLEAN NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE game_players (id UUID NOT NULL, pseudo_in_game VARCHAR(100) NOT NULL, avatar_in_game VARCHAR(255) DEFAULT NULL, is_host BOOLEAN NOT NULL, is_ready BOOLEAN NOT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, game_session_id UUID NOT NULL, user_id UUID DEFAULT NULL, character_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B38C3C898FE32B32 ON game_players (game_session_id)');
        $this->addSql('CREATE INDEX IDX_B38C3C89A76ED395 ON game_players (user_id)');
        $this->addSql('CREATE INDEX IDX_B38C3C891136BE75 ON game_players (character_id)');
        $this->addSql('CREATE TABLE game_ratings (id UUID NOT NULL, rating INT NOT NULL, rated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, game_session_id UUID NOT NULL, game_player_id UUID NOT NULL, murder_party_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5B7478E68FE32B32 ON game_ratings (game_session_id)');
        $this->addSql('CREATE INDEX IDX_5B7478E64B4034DD ON game_ratings (game_player_id)');
        $this->addSql('CREATE INDEX IDX_5B7478E6CC1959D0 ON game_ratings (murder_party_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_rating_per_player ON game_ratings (game_session_id, game_player_id)');
        $this->addSql('CREATE TABLE game_results (id UUID NOT NULL, success BOOLEAN NOT NULL, correct_votes_count INT NOT NULL, total_votes_count INT NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, game_session_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A619B3B8FE32B32 ON game_results (game_session_id)');
        $this->addSql('CREATE TABLE game_sessions (id UUID NOT NULL, join_code VARCHAR(8) NOT NULL, status VARCHAR(30) NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, timer_ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, voting_ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, murder_party_id UUID NOT NULL, host_user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31246235E64D7D01 ON game_sessions (join_code)');
        $this->addSql('CREATE INDEX IDX_31246235CC1959D0 ON game_sessions (murder_party_id)');
        $this->addSql('CREATE INDEX IDX_312462359092FFA4 ON game_sessions (host_user_id)');
        $this->addSql('CREATE TABLE game_votes (id UUID NOT NULL, voted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, game_session_id UUID NOT NULL, voter_game_player_id UUID NOT NULL, voted_character_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_63B803408FE32B32 ON game_votes (game_session_id)');
        $this->addSql('CREATE INDEX IDX_63B8034031805591 ON game_votes (voter_game_player_id)');
        $this->addSql('CREATE INDEX IDX_63B80340BEEE1DC1 ON game_votes (voted_character_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote_per_player ON game_votes (game_session_id, voter_game_player_id)');
        $this->addSql('CREATE TABLE murder_parties (id UUID NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, synopsis TEXT NOT NULL, scenario TEXT NOT NULL, epilogue TEXT NOT NULL, cover_image_url VARCHAR(255) DEFAULT NULL, duree INT NOT NULL, nb_players INT NOT NULL, price NUMERIC(10, 2) NOT NULL, is_free BOOLEAN NOT NULL, is_published BOOLEAN NOT NULL, average_rating NUMERIC(3, 2) NOT NULL, ratings_count INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DEA9B185989D9B62 ON murder_parties (slug)');
        $this->addSql('CREATE TABLE packs (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE pack_murder_parties (pack_id UUID NOT NULL, murder_party_id UUID NOT NULL, PRIMARY KEY (pack_id, murder_party_id))');
        $this->addSql('CREATE INDEX IDX_3CB953B51919B217 ON pack_murder_parties (pack_id)');
        $this->addSql('CREATE INDEX IDX_3CB953B5CC1959D0 ON pack_murder_parties (murder_party_id)');
        $this->addSql('CREATE TABLE promo_codes (id UUID NOT NULL, code VARCHAR(100) NOT NULL, discount_type VARCHAR(20) NOT NULL, discount_value NUMERIC(10, 2) NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, max_uses INT DEFAULT NULL, current_uses INT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C84FDDB77153098 ON promo_codes (code)');
        $this->addSql('CREATE TABLE purchases (id UUID NOT NULL, purchase_type VARCHAR(20) NOT NULL, amount_paid NUMERIC(10, 2) NOT NULL, discount_applied NUMERIC(10, 2) NOT NULL, payment_method VARCHAR(20) NOT NULL, stripe_payment_id VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, purchased_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, murder_party_id UUID DEFAULT NULL, pack_id UUID DEFAULT NULL, promo_code_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_AA6431FEA76ED395 ON purchases (user_id)');
        $this->addSql('CREATE INDEX IDX_AA6431FECC1959D0 ON purchases (murder_party_id)');
        $this->addSql('CREATE INDEX IDX_AA6431FE1919B217 ON purchases (pack_id)');
        $this->addSql('CREATE INDEX IDX_AA6431FE2FAE4625 ON purchases (promo_code_id)');
        $this->addSql('CREATE TABLE push_tokens (id UUID NOT NULL, token VARCHAR(255) NOT NULL, platform VARCHAR(10) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_81E0C0AEA76ED395 ON push_tokens (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_token ON push_tokens (user_id, token)');
        $this->addSql('CREATE TABLE reviews (id UUID NOT NULL, author_prenom VARCHAR(100) NOT NULL, author_nom VARCHAR(100) NOT NULL, author_email VARCHAR(180) NOT NULL, content TEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, user_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6970EB0FA76ED395 ON reviews (user_id)');
        $this->addSql('CREATE TABLE user_murder_parties (id UUID NOT NULL, is_played BOOLEAN NOT NULL, unlocked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, murder_party_id UUID NOT NULL, purchase_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B52C6EDCA76ED395 ON user_murder_parties (user_id)');
        $this->addSql('CREATE INDEX IDX_B52C6EDCCC1959D0 ON user_murder_parties (murder_party_id)');
        $this->addSql('CREATE INDEX IDX_B52C6EDC558FBEB9 ON user_murder_parties (purchase_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_murder_party ON user_murder_parties (user_id, murder_party_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) DEFAULT NULL, auth_provider VARCHAR(20) NOT NULL, auth_provider_id VARCHAR(255) DEFAULT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, pseudo VARCHAR(100) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, dob DATE NOT NULL, notifications BOOLEAN NOT NULL, role VARCHAR(20) NOT NULL, is_deleted BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410ECC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE clues ADD CONSTRAINT FK_EAFCA7E4CC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE clues ADD CONSTRAINT FK_EAFCA7E41136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_players ADD CONSTRAINT FK_B38C3C898FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_players ADD CONSTRAINT FK_B38C3C89A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_players ADD CONSTRAINT FK_B38C3C891136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_ratings ADD CONSTRAINT FK_5B7478E68FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_ratings ADD CONSTRAINT FK_5B7478E64B4034DD FOREIGN KEY (game_player_id) REFERENCES game_players (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_ratings ADD CONSTRAINT FK_5B7478E6CC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_results ADD CONSTRAINT FK_A619B3B8FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_sessions ADD CONSTRAINT FK_31246235CC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_sessions ADD CONSTRAINT FK_312462359092FFA4 FOREIGN KEY (host_user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_votes ADD CONSTRAINT FK_63B803408FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_votes ADD CONSTRAINT FK_63B8034031805591 FOREIGN KEY (voter_game_player_id) REFERENCES game_players (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE game_votes ADD CONSTRAINT FK_63B80340BEEE1DC1 FOREIGN KEY (voted_character_id) REFERENCES characters (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE pack_murder_parties ADD CONSTRAINT FK_3CB953B51919B217 FOREIGN KEY (pack_id) REFERENCES packs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pack_murder_parties ADD CONSTRAINT FK_3CB953B5CC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FECC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE1919B217 FOREIGN KEY (pack_id) REFERENCES packs (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE push_tokens ADD CONSTRAINT FK_81E0C0AEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_murder_parties ADD CONSTRAINT FK_B52C6EDCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_murder_parties ADD CONSTRAINT FK_B52C6EDCCC1959D0 FOREIGN KEY (murder_party_id) REFERENCES murder_parties (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_murder_parties ADD CONSTRAINT FK_B52C6EDC558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchases (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE characters DROP CONSTRAINT FK_3A29410ECC1959D0');
        $this->addSql('ALTER TABLE clues DROP CONSTRAINT FK_EAFCA7E4CC1959D0');
        $this->addSql('ALTER TABLE clues DROP CONSTRAINT FK_EAFCA7E41136BE75');
        $this->addSql('ALTER TABLE game_players DROP CONSTRAINT FK_B38C3C898FE32B32');
        $this->addSql('ALTER TABLE game_players DROP CONSTRAINT FK_B38C3C89A76ED395');
        $this->addSql('ALTER TABLE game_players DROP CONSTRAINT FK_B38C3C891136BE75');
        $this->addSql('ALTER TABLE game_ratings DROP CONSTRAINT FK_5B7478E68FE32B32');
        $this->addSql('ALTER TABLE game_ratings DROP CONSTRAINT FK_5B7478E64B4034DD');
        $this->addSql('ALTER TABLE game_ratings DROP CONSTRAINT FK_5B7478E6CC1959D0');
        $this->addSql('ALTER TABLE game_results DROP CONSTRAINT FK_A619B3B8FE32B32');
        $this->addSql('ALTER TABLE game_sessions DROP CONSTRAINT FK_31246235CC1959D0');
        $this->addSql('ALTER TABLE game_sessions DROP CONSTRAINT FK_312462359092FFA4');
        $this->addSql('ALTER TABLE game_votes DROP CONSTRAINT FK_63B803408FE32B32');
        $this->addSql('ALTER TABLE game_votes DROP CONSTRAINT FK_63B8034031805591');
        $this->addSql('ALTER TABLE game_votes DROP CONSTRAINT FK_63B80340BEEE1DC1');
        $this->addSql('ALTER TABLE pack_murder_parties DROP CONSTRAINT FK_3CB953B51919B217');
        $this->addSql('ALTER TABLE pack_murder_parties DROP CONSTRAINT FK_3CB953B5CC1959D0');
        $this->addSql('ALTER TABLE purchases DROP CONSTRAINT FK_AA6431FEA76ED395');
        $this->addSql('ALTER TABLE purchases DROP CONSTRAINT FK_AA6431FECC1959D0');
        $this->addSql('ALTER TABLE purchases DROP CONSTRAINT FK_AA6431FE1919B217');
        $this->addSql('ALTER TABLE purchases DROP CONSTRAINT FK_AA6431FE2FAE4625');
        $this->addSql('ALTER TABLE push_tokens DROP CONSTRAINT FK_81E0C0AEA76ED395');
        $this->addSql('ALTER TABLE reviews DROP CONSTRAINT FK_6970EB0FA76ED395');
        $this->addSql('ALTER TABLE user_murder_parties DROP CONSTRAINT FK_B52C6EDCA76ED395');
        $this->addSql('ALTER TABLE user_murder_parties DROP CONSTRAINT FK_B52C6EDCCC1959D0');
        $this->addSql('ALTER TABLE user_murder_parties DROP CONSTRAINT FK_B52C6EDC558FBEB9');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE clues');
        $this->addSql('DROP TABLE contact_messages');
        $this->addSql('DROP TABLE game_players');
        $this->addSql('DROP TABLE game_ratings');
        $this->addSql('DROP TABLE game_results');
        $this->addSql('DROP TABLE game_sessions');
        $this->addSql('DROP TABLE game_votes');
        $this->addSql('DROP TABLE murder_parties');
        $this->addSql('DROP TABLE packs');
        $this->addSql('DROP TABLE pack_murder_parties');
        $this->addSql('DROP TABLE promo_codes');
        $this->addSql('DROP TABLE purchases');
        $this->addSql('DROP TABLE push_tokens');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE user_murder_parties');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}