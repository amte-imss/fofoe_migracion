<?php

namespace App\Repository;

use App\Entity\Permiso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permiso::class);
    }

    /** Devuelve todos los permisos asociados a un rol por su clave */
    public function findByRolClave(string $clavRol): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.rol', 'r')
            ->where('r.clave = :clave')
            ->setParameter('clave', $clavRol)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Permiso[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.rol', 'r')
            ->orderBy('r.nombre', 'ASC')
            ->addOrderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
