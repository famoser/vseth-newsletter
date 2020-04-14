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
final class Version20200414061735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_2B219D7022DB1917');
        $this->addSql('DROP INDEX IDX_2B219D709E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__entry AS SELECT id, newsletter_id, organisation_id, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at, organizer FROM entry');
        $this->addSql('DROP TABLE entry');
        $this->addSql('CREATE TABLE entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, newsletter_id INTEGER DEFAULT NULL, organisation_id INTEGER DEFAULT NULL, title_de CLOB NOT NULL COLLATE BINARY, title_en CLOB NOT NULL COLLATE BINARY, description_de CLOB NOT NULL COLLATE BINARY, description_en CLOB NOT NULL COLLATE BINARY, link_de CLOB DEFAULT NULL COLLATE BINARY, link_en CLOB DEFAULT NULL COLLATE BINARY, location CLOB DEFAULT NULL COLLATE BINARY, priority INTEGER NOT NULL, approved_at DATETIME DEFAULT NULL, sent_at DATETIME DEFAULT NULL, reject_reason CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, organizer CLOB DEFAULT NULL COLLATE BINARY, start_date DATE DEFAULT NULL, start_time CLOB DEFAULT NULL, end_date DATE DEFAULT NULL, end_time CLOB DEFAULT NULL, CONSTRAINT FK_2B219D7022DB1917 FOREIGN KEY (newsletter_id) REFERENCES newsletter (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B219D709E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO entry (id, newsletter_id, organisation_id, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at, organizer) SELECT id, newsletter_id, organisation_id, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at, organizer FROM __temp__entry');
        $this->addSql('DROP TABLE __temp__entry');
        $this->addSql('CREATE INDEX IDX_2B219D7022DB1917 ON entry (newsletter_id)');
        $this->addSql('CREATE INDEX IDX_2B219D709E6B1585 ON entry (organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_2B219D7022DB1917');
        $this->addSql('DROP INDEX IDX_2B219D709E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__entry AS SELECT id, newsletter_id, organisation_id, organizer, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at FROM entry');
        $this->addSql('DROP TABLE entry');
        $this->addSql('CREATE TABLE entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, newsletter_id INTEGER DEFAULT NULL, organisation_id INTEGER DEFAULT NULL, organizer CLOB DEFAULT NULL, title_de CLOB NOT NULL, title_en CLOB NOT NULL, description_de CLOB NOT NULL, description_en CLOB NOT NULL, link_de CLOB DEFAULT NULL, link_en CLOB DEFAULT NULL, location CLOB DEFAULT NULL, priority INTEGER NOT NULL, approved_at DATETIME DEFAULT NULL, sent_at DATETIME DEFAULT NULL, reject_reason CLOB DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO entry (id, newsletter_id, organisation_id, organizer, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at) SELECT id, newsletter_id, organisation_id, organizer, title_de, title_en, description_de, description_en, link_de, link_en, location, priority, approved_at, sent_at, reject_reason, created_at, last_changed_at FROM __temp__entry');
        $this->addSql('DROP TABLE __temp__entry');
        $this->addSql('CREATE INDEX IDX_2B219D7022DB1917 ON entry (newsletter_id)');
        $this->addSql('CREATE INDEX IDX_2B219D709E6B1585 ON entry (organisation_id)');
    }
}
