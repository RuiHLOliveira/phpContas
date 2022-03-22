<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213171439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE movimento_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conta (id SERIAL NOT NULL, nome VARCHAR(255) NOT NULL, saldo NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE movimento (id INT NOT NULL, conta_id INT NOT NULL, descricao VARCHAR(255) NOT NULL, data DATE NOT NULL, valor NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5BE0E915628EE05C ON movimento (conta_id)');
        $this->addSql('ALTER TABLE movimento ADD CONSTRAINT FK_5BE0E915628EE05C FOREIGN KEY (conta_id) REFERENCES conta (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE movimento DROP CONSTRAINT FK_5BE0E915628EE05C');
        $this->addSql('DROP SEQUENCE movimento_id_seq CASCADE');
        $this->addSql('DROP TABLE conta');
        $this->addSql('DROP TABLE movimento');
    }
}
