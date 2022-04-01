<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220330100818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE item_movimento_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE item_movimento (id INT NOT NULL, movimento_id INT NOT NULL, nome VARCHAR(255) NOT NULL, valor NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_573AFFE5531A0E2D ON item_movimento (movimento_id)');
        $this->addSql('ALTER TABLE item_movimento ADD CONSTRAINT FK_573AFFE5531A0E2D FOREIGN KEY (movimento_id) REFERENCES movimento (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE item_movimento_id_seq CASCADE');
        $this->addSql('DROP TABLE item_movimento');
    }
}
