<?php

namespace App\Repository;

use App\Entity\ConfiguracionGlobal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConfiguracionGlobalRepository extends ServiceEntityRepository
{
    const MONTO_EF_ALUMNO_EXTERNO        = 'MONTO_EF_ALUMNO_EXTERNO';
    const MONTO_EF_ALUMNO_HIJO_TRABAJADOR = 'MONTO_EF_ALUMNO_HIJO_TRABAJADOR';
    const MONTO_EF_EXTRAORDINARIO        = 'MONTO_EF_ALUMNO_EXTRAORDINARIO';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfiguracionGlobal::class);
    }

    public function findMontoEF(string $tipo): ?ConfiguracionGlobal
    {
        $tipo = mb_strtolower(trim($tipo));

        if ($tipo === 'externo') {
            $clave = self::MONTO_EF_ALUMNO_EXTERNO;
        } elseif (in_array($tipo, ['hijo_trabajador', 'hijo de trabajador'])) {
            $clave = self::MONTO_EF_ALUMNO_HIJO_TRABAJADOR;
        } elseif ($tipo === 'extraordinario') {
            $clave = self::MONTO_EF_EXTRAORDINARIO;
        } else {
            return null;
        }

        return $this->findOneBy(['clave' => $clave]);
    }
}
