<?php

namespace App\Repository;

use App\Entity\ConfiguracionGlobal;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<ConfiguracionGlobal>
 */
interface ConfiguracionGlobalRepositoryInterface extends ObjectRepository
{
    const MONTO_EF_ALUMNO_EXTERNO         = 'MONTO_EF_ALUMNO_EXTERNO';
    const MONTO_EF_ALUMNO_HIJO_TRABAJADOR = 'MONTO_EF_ALUMNO_HIJO_TRABAJADOR';
    const MONTO_EF_EXTRAORDINARIO         = 'MONTO_EF_ALUMNO_EXTRAORDINARIO';
    const MONTO_EF_POSGRADO_ALUMNO_EXTERNO = 'MONTO_EF_POSGRADO_ALUMNO_EXTERNO';
    const MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR = 'MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR';

    public function findMontoEF(string $clave, string $type = \App\Entity\Enfermeria\Solicitud::TYPE_LICENCIATURA): ?ConfiguracionGlobal;

    public function findEFMontoAlumnoExterno(): ?ConfiguracionGlobal;

    public function findEFMontoAlumnoHijoTrabajador(): ?ConfiguracionGlobal;
}
