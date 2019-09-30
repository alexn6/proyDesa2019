<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190929162958 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, deporte_id INT DEFAULT NULL, nombre VARCHAR(127) NOT NULL, descripcion VARCHAR(255) NOT NULL, min_integrantes INT NOT NULL, UNIQUE INDEX UNIQ_4E10122D3A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competencia (id INT AUTO_INCREMENT NOT NULL, categoria_id INT DEFAULT NULL, nombre VARCHAR(127) NOT NULL, fecha_ini DATE NOT NULL, fecha_fin DATE NOT NULL, max_competidores INT NOT NULL, UNIQUE INDEX UNIQ_842C498A3A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(127) NOT NULL, nombre VARCHAR(127) NOT NULL, apellido VARCHAR(127) NOT NULL, correo VARCHAR(127) NOT NULL, pass VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2265B05DD67CF11D (nombre_usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deporte (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(127) NOT NULL, UNIQUE INDEX UNIQ_1C5BBE03A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_competencia (id INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_competencia INT NOT NULL, rol VARCHAR(127) NOT NULL, INDEX IDX_BC07BB04FCF8192D (id_usuario), INDEX IDX_BC07BB049C3E847D (id_competencia), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categoria ADD CONSTRAINT FK_4E10122D239C54DD FOREIGN KEY (deporte_id) REFERENCES deporte (id)');
        $this->addSql('ALTER TABLE competencia ADD CONSTRAINT FK_842C498A3397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id)');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB04FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB049C3E847D FOREIGN KEY (id_competencia) REFERENCES competencia (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competencia DROP FOREIGN KEY FK_842C498A3397707A');
        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB049C3E847D');
        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB04FCF8192D');
        $this->addSql('ALTER TABLE categoria DROP FOREIGN KEY FK_4E10122D239C54DD');
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE competencia');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE deporte');
        $this->addSql('DROP TABLE usuario_competencia');
    }
}
