<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024031637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rol (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(32) NOT NULL, UNIQUE INDEX UNIQ_E553F373A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario_competencia ADD rol_id INT DEFAULT NULL, DROP rol');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB044BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id)');
        $this->addSql('CREATE INDEX IDX_BC07BB044BAB96C ON usuario_competencia (rol_id)');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX fk_bc07bb04fcf8192d TO IDX_BC07BB04FCF8192D');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX fk_bc07bb049c3e847d TO IDX_BC07BB049C3E847D');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF73525A20332D99 ON tipo_organizacion (codigo)');
        $this->addSql('ALTER TABLE categoria RENAME INDEX fk_4e10122d239c54dd TO IDX_4E10122D239C54DD');
        $this->addSql('ALTER TABLE competencia CHANGE genero genero ENUM(\'MASCULINO\', \'FEMENINO\', \'MIXTO\'), CHANGE cant_grupos cant_grupos INT NOT NULL');
        $this->addSql('ALTER TABLE competencia RENAME INDEX fk_842c498a3397707a TO IDX_842C498A3397707A');
        $this->addSql('ALTER TABLE competencia RENAME INDEX fk_842c498a90b1019e TO IDX_842C498A90B1019E');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB044BAB96C');
        $this->addSql('DROP TABLE rol');
        $this->addSql('ALTER TABLE categoria RENAME INDEX idx_4e10122d239c54dd TO FK_4E10122D239C54DD');
        $this->addSql('ALTER TABLE competencia CHANGE genero genero VARCHAR(127) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE cant_grupos cant_grupos INT DEFAULT NULL');
        $this->addSql('ALTER TABLE competencia RENAME INDEX idx_842c498a3397707a TO FK_842C498A3397707A');
        $this->addSql('ALTER TABLE competencia RENAME INDEX idx_842c498a90b1019e TO FK_842C498A90B1019E');
        $this->addSql('DROP INDEX UNIQ_BF73525A20332D99 ON tipo_organizacion');
        $this->addSql('DROP INDEX IDX_BC07BB044BAB96C ON usuario_competencia');
        $this->addSql('ALTER TABLE usuario_competencia ADD rol VARCHAR(127) NOT NULL COLLATE utf8mb4_unicode_ci, DROP rol_id');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX idx_bc07bb049c3e847d TO FK_BC07BB049C3E847D');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX idx_bc07bb04fcf8192d TO FK_BC07BB04FCF8192D');
    }
}
