<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250819080802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD name VARCHAR(255) DEFAULT NULL, ADD age INT DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD country VARCHAR(255) DEFAULT NULL, ADD about LONGTEXT DEFAULT NULL, ADD avatar VARCHAR(255) DEFAULT NULL, ADD badges JSON DEFAULT NULL, ADD interests JSON DEFAULT NULL, ADD gallery JSON DEFAULT NULL, ADD height INT DEFAULT NULL, ADD weight INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP name, DROP age, DROP city, DROP country, DROP about, DROP avatar, DROP badges, DROP interests, DROP gallery, DROP height, DROP weight');
    }
}
