<?php

declare(strict_types=1);

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324191032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, newsletter_id INTEGER DEFAULT NULL, organisation_id INTEGER DEFAULT NULL, organizer CLOB NOT NULL, title_de CLOB NOT NULL, title_en CLOB NOT NULL, description_de CLOB NOT NULL, description_en CLOB NOT NULL, link_de CLOB DEFAULT NULL, link_en CLOB DEFAULT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, location CLOB DEFAULT NULL, priority INTEGER NOT NULL, approved_at DATETIME DEFAULT NULL, sent_at DATETIME DEFAULT NULL, reject_reason CLOB DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_2B219D7022DB1917 ON entry (newsletter_id)');
        $this->addSql('CREATE INDEX IDX_2B219D709E6B1585 ON entry (organisation_id)');
        $this->addSql('CREATE TABLE organisation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name CLOB NOT NULL, email CLOB NOT NULL, category INTEGER NOT NULL, comments CLOB DEFAULT NULL, authentication_code VARCHAR(255) NOT NULL, last_visit_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, hidden_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE newsletter (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, planned_send_at DATETIME DEFAULT NULL, sent_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE entry');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE newsletter');
    }
}
