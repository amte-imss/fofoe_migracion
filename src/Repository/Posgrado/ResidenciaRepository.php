<?php

namespace App\Repository\Posgrado;

use App\Entity\Posgrado\Residencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Residencia>
 */
class ResidenciaRepository extends ServiceEntityRepository implements ResidenciaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Residencia::class);
    }

    public function getCiclos(string $tipoResidencia = ''): array
    {
        $qb = $this->createQueryBuilder('residencia')
            ->select('residencia.ciclo')
            ->leftJoin('residencia.cargaMasiva', 'carga')
            ->distinct()
            ->where('carga.deleted_at IS NULL AND residencia.deleted_at IS NULL')
            ->orderBy('residencia.ciclo', 'DESC');

        if ($tipoResidencia !== '') {
            $qb->andWhere('residencia.tipo = :tipo')
                ->setParameter('tipo', $tipoResidencia);
        }

        return $qb->getQuery()->getResult();
    }

    public function getFoliosYaExistentes(array $folios, string $tipo): array
    {
        return $this->createQueryBuilder('residencia')
            ->select('residencia.folio')
            ->leftJoin('residencia.cargaMasiva', 'carga')
            ->where('carga.deleted_at IS NULL AND residencia.deleted_at IS NULL')
            ->andWhere('residencia.folio IN (:folios)')
            ->andWhere('residencia.tipo = :tipo')
            ->setParameter('folios', $folios)
            ->setParameter('tipo', $tipo)
            ->getQuery()
            ->getResult();
    }
}
