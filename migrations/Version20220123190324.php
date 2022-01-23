<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220123190324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist (id INT UNSIGNED AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, followers_count INT NOT NULL, ext_id INT NOT NULL, UNIQUE INDEX UNIQ_1599687F85E0677 (username), UNIQUE INDEX UNIQ_15996875D04BFAD (ext_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE track (id INT UNSIGNED AUTO_INCREMENT NOT NULL, artist_id INT UNSIGNED NOT NULL, title VARCHAR(512) NOT NULL, duration INT NOT NULL, playback_count INT NOT NULL, comment_count INT NOT NULL, ext_id INT NOT NULL, UNIQUE INDEX UNIQ_D6E3F8A65D04BFAD (ext_id), INDEX IDX_D6E3F8A6B7970CF8 (artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6B7970CF8');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE track');
    }
}
