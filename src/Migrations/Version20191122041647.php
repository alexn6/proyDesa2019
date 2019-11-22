<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191122041647 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE usuario_competencia (id INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_competencia INT NOT NULL, rol_id INT DEFAULT NULL, alias VARCHAR(127) DEFAULT NULL, INDEX IDX_BC07BB04FCF8192D (id_usuario), INDEX IDX_BC07BB049C3E847D (id_competencia), INDEX IDX_BC07BB044BAB96C (rol_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jornada (id INT AUTO_INCREMENT NOT NULL, competencia_id INT DEFAULT NULL, numero INT NOT NULL, fecha DATE DEFAULT NULL, INDEX IDX_61D21CBF9980C34D (competencia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_organizacion (id INT AUTO_INCREMENT NOT NULL, codigo VARCHAR(10) NOT NULL, nombre VARCHAR(127) NOT NULL, descripcion VARCHAR(255) NOT NULL, minimo VARCHAR(127) NOT NULL, UNIQUE INDEX UNIQ_BF73525A20332D99 (codigo), UNIQUE INDEX UNIQ_BF73525A3A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE encuentro (id INT AUTO_INCREMENT NOT NULL, competencia_id INT NOT NULL, compuser1_id INT NOT NULL, compuser2_id INT NOT NULL, jornada_id INT NOT NULL, juez_id INT DEFAULT NULL, campo_id INT DEFAULT NULL, turno_id INT DEFAULT NULL, grupo INT DEFAULT NULL, rdo_comp1 INT DEFAULT NULL, rdo_comp2 INT DEFAULT NULL, INDEX IDX_CDFA77FA9980C34D (competencia_id), INDEX IDX_CDFA77FA3AD9E (compuser1_id), INDEX IDX_CDFA77FA12B60270 (compuser2_id), INDEX IDX_CDFA77FA26E992D9 (jornada_id), INDEX IDX_CDFA77FA2515F440 (juez_id), INDEX IDX_CDFA77FAA17A385C (campo_id), INDEX IDX_CDFA77FA69C5211E (turno_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, deporte_id INT DEFAULT NULL, nombre VARCHAR(127) NOT NULL, descripcion VARCHAR(255) NOT NULL, min_integrantes INT NOT NULL, UNIQUE INDEX UNIQ_4E10122D3A909126 (nombre), INDEX IDX_4E10122D239C54DD (deporte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE juez (id INT AUTO_INCREMENT NOT NULL, competencia_id INT DEFAULT NULL, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, dni INT NOT NULL, INDEX IDX_8FBF65009980C34D (competencia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE predio (id INT AUTO_INCREMENT NOT NULL, competencia_id INT DEFAULT NULL, nombre VARCHAR(50) NOT NULL, direccion VARCHAR(150) NOT NULL, ciudad VARCHAR(150) NOT NULL, INDEX IDX_13E6D7279980C34D (competencia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE turno (id INT AUTO_INCREMENT NOT NULL, competencia_id INT DEFAULT NULL, hora_desde TIME NOT NULL, hora_hasta TIME NOT NULL, INDEX IDX_E79767629980C34D (competencia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rol (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(32) NOT NULL, UNIQUE INDEX UNIQ_E553F373A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE campo (id INT AUTO_INCREMENT NOT NULL, predio_id INT DEFAULT NULL, nombre VARCHAR(50) NOT NULL, capacidad INT DEFAULT NULL, dimensiones INT DEFAULT NULL, INDEX IDX_291737AADC5381D3 (predio_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competencia (id INT AUTO_INCREMENT NOT NULL, categoria_id INT DEFAULT NULL, organizacion_id INT DEFAULT NULL, nombre VARCHAR(127) NOT NULL, fecha_ini DATE NOT NULL, fecha_fin DATE NOT NULL, ciudad VARCHAR(127) NOT NULL, genero ENUM(\'MASCULINO\', \'FEMENINO\', \'MIXTO\'), max_competidores INT DEFAULT NULL, cant_grupos INT DEFAULT NULL, fase INT DEFAULT NULL, min_competidores INT DEFAULT NULL, fase_actual INT NOT NULL, UNIQUE INDEX UNIQ_842C498A3A909126 (nombre), INDEX IDX_842C498A3397707A (categoria_id), INDEX IDX_842C498A90B1019E (organizacion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deporte (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(127) NOT NULL, UNIQUE INDEX UNIQ_1C5BBE03A909126 (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(127) NOT NULL, nombre VARCHAR(127) NOT NULL, apellido VARCHAR(127) NOT NULL, correo VARCHAR(127) NOT NULL, pass VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2265B05DD67CF11D (nombre_usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB04FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB049C3E847D FOREIGN KEY (id_competencia) REFERENCES competencia (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_competencia ADD CONSTRAINT FK_BC07BB044BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id)');
        $this->addSql('ALTER TABLE jornada ADD CONSTRAINT FK_61D21CBF9980C34D FOREIGN KEY (competencia_id) REFERENCES competencia (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA9980C34D FOREIGN KEY (competencia_id) REFERENCES competencia (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA3AD9E FOREIGN KEY (compuser1_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA12B60270 FOREIGN KEY (compuser2_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA26E992D9 FOREIGN KEY (jornada_id) REFERENCES jornada (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA2515F440 FOREIGN KEY (juez_id) REFERENCES juez (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FAA17A385C FOREIGN KEY (campo_id) REFERENCES campo (id)');
        $this->addSql('ALTER TABLE encuentro ADD CONSTRAINT FK_CDFA77FA69C5211E FOREIGN KEY (turno_id) REFERENCES turno (id)');
        $this->addSql('ALTER TABLE categoria ADD CONSTRAINT FK_4E10122D239C54DD FOREIGN KEY (deporte_id) REFERENCES deporte (id)');
        $this->addSql('ALTER TABLE juez ADD CONSTRAINT FK_8FBF65009980C34D FOREIGN KEY (competencia_id) REFERENCES competencia (id)');
        $this->addSql('ALTER TABLE predio ADD CONSTRAINT FK_13E6D7279980C34D FOREIGN KEY (competencia_id) REFERENCES competencia (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E79767629980C34D FOREIGN KEY (competencia_id) REFERENCES competencia (id)');
        $this->addSql('ALTER TABLE campo ADD CONSTRAINT FK_291737AADC5381D3 FOREIGN KEY (predio_id) REFERENCES predio (id)');
        $this->addSql('ALTER TABLE competencia ADD CONSTRAINT FK_842C498A3397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id)');
        $this->addSql('ALTER TABLE competencia ADD CONSTRAINT FK_842C498A90B1019E FOREIGN KEY (organizacion_id) REFERENCES tipo_organizacion (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA26E992D9');
        $this->addSql('ALTER TABLE competencia DROP FOREIGN KEY FK_842C498A90B1019E');
        $this->addSql('ALTER TABLE competencia DROP FOREIGN KEY FK_842C498A3397707A');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA2515F440');
        $this->addSql('ALTER TABLE campo DROP FOREIGN KEY FK_291737AADC5381D3');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA69C5211E');
        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB044BAB96C');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FAA17A385C');
        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB049C3E847D');
        $this->addSql('ALTER TABLE jornada DROP FOREIGN KEY FK_61D21CBF9980C34D');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA9980C34D');
        $this->addSql('ALTER TABLE juez DROP FOREIGN KEY FK_8FBF65009980C34D');
        $this->addSql('ALTER TABLE predio DROP FOREIGN KEY FK_13E6D7279980C34D');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E79767629980C34D');
        $this->addSql('ALTER TABLE categoria DROP FOREIGN KEY FK_4E10122D239C54DD');
        $this->addSql('ALTER TABLE usuario_competencia DROP FOREIGN KEY FK_BC07BB04FCF8192D');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA3AD9E');
        $this->addSql('ALTER TABLE encuentro DROP FOREIGN KEY FK_CDFA77FA12B60270');
        $this->addSql('DROP TABLE usuario_competencia');
        $this->addSql('DROP TABLE jornada');
        $this->addSql('DROP TABLE tipo_organizacion');
        $this->addSql('DROP TABLE encuentro');
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE juez');
        $this->addSql('DROP TABLE predio');
        $this->addSql('DROP TABLE turno');
        $this->addSql('DROP TABLE rol');
        $this->addSql('DROP TABLE campo');
        $this->addSql('DROP TABLE competencia');
        $this->addSql('DROP TABLE deporte');
        $this->addSql('DROP TABLE usuario');
    }
}
