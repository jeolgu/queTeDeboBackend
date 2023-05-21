<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230521172226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cobro (id INT AUTO_INCREMENT NOT NULL, creador_id INT NOT NULL, receptor_id INT NOT NULL, creacion DATETIME NOT NULL, titulo VARCHAR(255) NOT NULL, texto LONGTEXT DEFAULT NULL, revisado TINYINT(1) DEFAULT 0 NOT NULL, completado TINYINT(1) DEFAULT 0 NOT NULL, fecha_completado DATETIME DEFAULT NULL, archivado TINYINT(1) DEFAULT 0 NOT NULL, importe DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F0A2652662F40C3D (creador_id), UNIQUE INDEX UNIQ_F0A26526386D8D01 (receptor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE datos_personales (id_usuario_id INT NOT NULL, nombre VARCHAR(255) DEFAULT NULL, apellidos VARCHAR(255) DEFAULT NULL, edad INT DEFAULT NULL, localidad VARCHAR(255) DEFAULT NULL, cp VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, pais VARCHAR(255) DEFAULT NULL, idioma_predefinido VARCHAR(5) DEFAULT \'ES\' NOT NULL, PRIMARY KEY(id_usuario_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, token VARCHAR(255) NOT NULL, expiracion DATETIME NOT NULL, INDEX IDX_5F37A13B7EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cobro ADD CONSTRAINT FK_F0A2652662F40C3D FOREIGN KEY (creador_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cobro ADD CONSTRAINT FK_F0A26526386D8D01 FOREIGN KEY (receptor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE datos_personales ADD CONSTRAINT FK_87E529407EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13B7EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cobro DROP FOREIGN KEY FK_F0A2652662F40C3D');
        $this->addSql('ALTER TABLE cobro DROP FOREIGN KEY FK_F0A26526386D8D01');
        $this->addSql('ALTER TABLE datos_personales DROP FOREIGN KEY FK_87E529407EB2C349');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13B7EB2C349');
        $this->addSql('DROP TABLE cobro');
        $this->addSql('DROP TABLE datos_personales');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE user');
    }
}
