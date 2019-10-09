<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191009040933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competencia ADD cant_grupos INT NULL');
        $this->addSql('ALTER TABLE tipo_organizacion ADD codigo VARCHAR(10) NOT NULL');
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categoria DROP INDEX UNIQ_4E10122D239C54DD, ADD INDEX FK_4E10122D239C54DD (deporte_id)');
        $this->addSql('ALTER TABLE competencia DROP INDEX UNIQ_842C498A3397707A, ADD INDEX FK_842C498A3397707A (categoria_id)');
        $this->addSql('ALTER TABLE competencia DROP INDEX UNIQ_842C498A90B1019E, ADD INDEX FK_842C498A90B1019E (organizacion_id)');
        $this->addSql('ALTER TABLE competencia CHANGE cant_grupos cant_grupos INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_BF73525A20332D99 ON tipo_organizacion');
        $this->addSql('ALTER TABLE tipo_organizacion DROP codigo');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX idx_bc07bb04fcf8192d TO FK_BC07BB04FCF8192D');
        $this->addSql('ALTER TABLE usuario_competencia RENAME INDEX idx_bc07bb049c3e847d TO FK_BC07BB049C3E847D');
    }
}
