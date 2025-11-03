<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103100841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `like` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, album VARCHAR(255) DEFAULT NULL, duration INT DEFAULT NULL, spotify_url VARCHAR(255) NOT NULL, datetime_immutable DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tracks ADD duration_ms INT DEFAULT NULL, ADD explicit TINYINT(1) DEFAULT NULL, ADD isrc LONGTEXT DEFAULT NULL, ADD href LONGTEXT DEFAULT NULL, ADD spotify_id LONGTEXT DEFAULT NULL, ADD is_local TINYINT(1) DEFAULT NULL, ADD name LONGTEXT DEFAULT NULL, ADD popularity INT DEFAULT NULL, ADD preview_url LONGTEXT DEFAULT NULL, ADD track_number INT DEFAULT NULL, ADD type LONGTEXT DEFAULT NULL, ADD uri LONGTEXT DEFAULT NULL, ADD picture_link LONGTEXT DEFAULT NULL, DROP title, DROP artist, DROP album, DROP created_at, CHANGE spotify_url spotify_url LONGTEXT NOT NULL, CHANGE duration disc_number INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `like`');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE tracks ADD title VARCHAR(255) NOT NULL, ADD artist VARCHAR(255) NOT NULL, ADD album VARCHAR(255) DEFAULT NULL, ADD duration INT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, DROP disc_number, DROP duration_ms, DROP explicit, DROP isrc, DROP href, DROP spotify_id, DROP is_local, DROP name, DROP popularity, DROP preview_url, DROP track_number, DROP type, DROP uri, DROP picture_link, CHANGE spotify_url spotify_url VARCHAR(255) DEFAULT NULL');
    }
}
