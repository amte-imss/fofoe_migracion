<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separa las solicitudes de Escuela de Enfermería en licenciatura y posgrado e incorpora las cuotas de posgrado.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE lote_escuelas_enfermeria ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'licenciatura'");
        $this->addSql("ALTER TABLE lote_escuelas_enfermeria ADD CONSTRAINT lote_escuelas_enfermeria_type_check CHECK (type IN ('licenciatura', 'posgrado'))");
        $this->addSql("INSERT INTO configuracion_global (clave, valor, descripcion, activo) SELECT 'MONTO_EF_POSGRADO_ALUMNO_EXTERNO', '3600', 'Cuota cuatrimestral de posgrado en Enfermería para alumno externo', true WHERE NOT EXISTS (SELECT 1 FROM configuracion_global WHERE clave = 'MONTO_EF_POSGRADO_ALUMNO_EXTERNO')");
        $this->addSql("INSERT INTO configuracion_global (clave, valor, descripcion, activo) SELECT 'MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR', '1800', 'Cuota cuatrimestral de posgrado en Enfermería para hijo de trabajador IMSS', true WHERE NOT EXISTS (SELECT 1 FROM configuracion_global WHERE clave = 'MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM configuracion_global WHERE clave IN ('MONTO_EF_POSGRADO_ALUMNO_EXTERNO', 'MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR')");
        $this->addSql('ALTER TABLE lote_escuelas_enfermeria DROP CONSTRAINT lote_escuelas_enfermeria_type_check');
        $this->addSql('ALTER TABLE lote_escuelas_enfermeria DROP COLUMN type');
    }
}
