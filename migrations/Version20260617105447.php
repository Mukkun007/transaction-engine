<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617105447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_clients (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, api_key VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EDF65A13C912ED9D (api_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE audit_logs (id BINARY(16) NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id VARCHAR(255) NOT NULL, action VARCHAR(255) NOT NULL, `before` JSON DEFAULT NULL, after JSON NOT NULL, performed_by VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entries (id BINARY(16) NOT NULL, type VARCHAR(255) NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL, account_id BINARY(16) NOT NULL, transaction_id BINARY(16) NOT NULL, INDEX IDX_2DF8B3C59B6B5FBA (account_id), INDEX IDX_2DF8B3C52FC0CB0F (transaction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE idempotency_keys (id BINARY(16) NOT NULL, key_value VARCHAR(255) NOT NULL, payload JSON NOT NULL, response_code INT NOT NULL, response_body JSON NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, api_client_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_F3C9CAF9FF4925FB (key_value), INDEX IDX_F3C9CAF9643FCC5A (api_client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE outbox_messages (id BINARY(16) NOT NULL, event_type VARCHAR(255) NOT NULL, payload JSON NOT NULL, status VARCHAR(255) NOT NULL, attempts INT NOT NULL, last_attempt_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transactions (id BINARY(16) NOT NULL, reference VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, related_transaction_id BINARY(16) DEFAULT NULL, api_client_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_EAA81A4CAEA34913 (reference), INDEX IDX_EAA81A4C4F981710 (related_transaction_id), INDEX IDX_EAA81A4C643FCC5A (api_client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C59B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id)');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C52FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id)');
        $this->addSql('ALTER TABLE idempotency_keys ADD CONSTRAINT FK_F3C9CAF9643FCC5A FOREIGN KEY (api_client_id) REFERENCES api_clients (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C4F981710 FOREIGN KEY (related_transaction_id) REFERENCES transactions (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C643FCC5A FOREIGN KEY (api_client_id) REFERENCES api_clients (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entries DROP FOREIGN KEY FK_2DF8B3C59B6B5FBA');
        $this->addSql('ALTER TABLE entries DROP FOREIGN KEY FK_2DF8B3C52FC0CB0F');
        $this->addSql('ALTER TABLE idempotency_keys DROP FOREIGN KEY FK_F3C9CAF9643FCC5A');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C4F981710');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C643FCC5A');
        $this->addSql('DROP TABLE api_clients');
        $this->addSql('DROP TABLE audit_logs');
        $this->addSql('DROP TABLE entries');
        $this->addSql('DROP TABLE idempotency_keys');
        $this->addSql('DROP TABLE outbox_messages');
        $this->addSql('DROP TABLE transactions');
    }
}
