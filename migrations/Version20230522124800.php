<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522124800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cobro (id INT AUTO_INCREMENT NOT NULL, creador_id INT NOT NULL, receptor_id INT NOT NULL, creacion DATETIME NOT NULL, titulo VARCHAR(255) NOT NULL, texto LONGTEXT DEFAULT NULL, revisado TINYINT(1) DEFAULT 0, completado TINYINT(1) DEFAULT 0, fecha_completado DATETIME DEFAULT NULL, archivado TINYINT(1) DEFAULT 0, importe DOUBLE PRECISION NOT NULL, INDEX IDX_F0A2652662F40C3D (creador_id), INDEX IDX_F0A26526386D8D01 (receptor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cobro ADD CONSTRAINT FK_F0A2652662F40C3D FOREIGN KEY (creador_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cobro ADD CONSTRAINT FK_F0A26526386D8D01 FOREIGN KEY (receptor_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cobro DROP FOREIGN KEY FK_F0A2652662F40C3D');
        $this->addSql('ALTER TABLE cobro DROP FOREIGN KEY FK_F0A26526386D8D01');
        $this->addSql('DROP TABLE cobro');
    }
}
