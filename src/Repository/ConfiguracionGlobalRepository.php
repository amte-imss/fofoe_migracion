<?php

namespace App\Repository;

use App\Entity\ConfiguracionGlobal;
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

    public function findMontoEF(string $clave): ?ConfiguracionGlobal
    {
        return match(mb_strtolower($clave)) {
            'externo'                        => $this->findEFMontoAlumnoExterno(),
            'hijo_trabajador',
            'hijo de trabajador'             => $this->findEFMontoAlumnoHijoTrabajador(),
            'extraordinario'                 => $this->findByClave(ConfiguracionGlobalRepositoryInterface::MONTO_EF_EXTRAORDINARIO),
            default                          => null,
        };
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
