<?php

namespace App\Repository;

use App\Entity\ConfiguracionGlobal;
use App\Entity\Enfermeria\Solicitud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConfiguracionGlobalRepository extends ServiceEntityRepository implements ConfiguracionGlobalRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfiguracionGlobal::class);
    }

    public function findEFMontoAlumnoExterno(): ?ConfiguracionGlobal
    {
        return $this->findByClave(ConfiguracionGlobalRepositoryInterface::MONTO_EF_ALUMNO_EXTERNO);
    }

    public function findEFMontoAlumnoHijoTrabajador(): ?ConfiguracionGlobal
    {
        return $this->findByClave(ConfiguracionGlobalRepositoryInterface::MONTO_EF_ALUMNO_HIJO_TRABAJADOR);
    }

    public function findMontoEF(string $clave, string $type = Solicitud::TYPE_LICENCIATURA): ?ConfiguracionGlobal
    {
        $montos = match ($type) {
            Solicitud::TYPE_POSGRADO => [
                'externo' => self::MONTO_EF_POSGRADO_ALUMNO_EXTERNO,
                'hijo_trabajador' => self::MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR,
                'hijo de trabajador' => self::MONTO_EF_POSGRADO_ALUMNO_HIJO_TRABAJADOR,
            ],
            Solicitud::TYPE_LICENCIATURA => [
                'externo' => self::MONTO_EF_ALUMNO_EXTERNO,
                'hijo_trabajador' => self::MONTO_EF_ALUMNO_HIJO_TRABAJADOR,
                'hijo de trabajador' => self::MONTO_EF_ALUMNO_HIJO_TRABAJADOR,
                'extraordinario' => self::MONTO_EF_EXTRAORDINARIO,
            ],
            default => [],
        };

        $key = $montos[mb_strtolower(trim($clave))] ?? null;
        return $key ? $this->findByClave($key) : null;
    }

    private function findByClave(string $clave): ?ConfiguracionGlobal
    {
        return $this->createQueryBuilder('cg')
            ->where('cg.clave = :clave')
            ->setParameter('clave', $clave)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
