<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 * Cambiado el datos de la columna "pass" nde la tabla usuario
 */
final class Version20190919140351 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE usuario CHANGE pass pass VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categoria DROP INDEX UNIQ_4E10122D239C54DD, ADD INDEX FK_4E10122D239C54DD (deporte_id)');
        $this->addSql('ALTER TABLE competencia DROP INDEX UNIQ_842C498A3397707A, ADD INDEX FK_842C498A3397707A (categoria_id)');
        $this->addSql('ALTER TABLE usuario CHANGE pass pass VARCHAR(127) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
