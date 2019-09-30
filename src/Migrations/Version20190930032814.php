<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 * Agregada la tabla de tipo de organizacion (se borraron indices q se habian creado auto)
 */
final class Version20190930032814 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE tipo_organizacion (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(127) NOT NULL, descripcion VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BF73525A3A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competencia ADD organizacion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE competencia ADD CONSTRAINT FK_842C498A90B1019E FOREIGN KEY (organizacion_id) REFERENCES tipo_organizacion (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_842C498A90B1019E ON competencia (organizacion_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competencia DROP FOREIGN KEY FK_842C498A90B1019E');
        $this->addSql('DROP TABLE tipo_organizacion');
        $this->addSql('ALTER TABLE categoria DROP INDEX UNIQ_4E10122D239C54DD, ADD INDEX FK_4E10122D239C54DD (deporte_id)');
        $this->addSql('ALTER TABLE competencia DROP INDEX UNIQ_842C498A3397707A, ADD INDEX FK_842C498A3397707A (categoria_id)');
        $this->addSql('DROP INDEX UNIQ_842C498A90B1019E ON competencia');
        $this->addSql('ALTER TABLE competencia DROP organizacion_id');
    }
}
