<?php

namespace App\Repository;

use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rol::class);
    }

    public function findByClave(string $clave): ?Rol
    {
        return $this->findOneBy(['clave' => $clave]);
    }

    /** @return Rol[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
