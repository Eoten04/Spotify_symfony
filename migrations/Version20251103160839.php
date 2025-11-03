<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103160839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users_tracks (user_id INT NOT NULL, track_id INT NOT NULL, INDEX IDX_2B8AA4BEA76ED395 (user_id), INDEX IDX_2B8AA4BE5ED23C43 (track_id), PRIMARY KEY(user_id, track_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users_tracks ADD CONSTRAINT FK_2B8AA4BEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_tracks ADD CONSTRAINT FK_2B8AA4BE5ED23C43 FOREIGN KEY (track_id) REFERENCES tracks (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users_tracks DROP FOREIGN KEY FK_2B8AA4BEA76ED395');
        $this->addSql('ALTER TABLE users_tracks DROP FOREIGN KEY FK_2B8AA4BE5ED23C43');
        $this->addSql('DROP TABLE users_tracks');
    }
}
