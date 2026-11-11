<?php

namespace App\Repository\Posgrado;

use App\Entity\Posgrado\CargaMasiva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CargaMasiva>
 */
class CargaMasivaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CargaMasiva::class);
    }

    /**
     * @return CargaMasiva[]
     */
    public function getPendienteEnvioEmailBienvenida(): array
    {
        return $this->createQueryBuilder('carga')
            ->where('carga.deleted_at IS NULL')
            ->getQuery()
            ->getResult();
    }
}
