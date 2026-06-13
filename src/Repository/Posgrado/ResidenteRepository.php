<?php

namespace App\Repository\Posgrado;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResidenteRepository extends ServiceEntityRepository implements ResidenteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Posgrado\Residente::class);
    }

    public function getResidentesExtranjeros(array $filters = [], int $perPage = 30, int $page = 1): array
    {
        $qb = $this->createQueryBuilder('residente')
            ->join('residente.residencias', 'residencia')
            ->join('residente.usuario', 'usuario')
            ->join('residencia.pagos', 'pago')
            ->andWhere('residencia.isTest = false');

        if (!empty($filters['ciclo'])) {
            $qb->andWhere('residencia.ciclo = :ciclo')
                ->setParameter('ciclo', $filters['ciclo']);
        }

        if (array_key_exists('tipo', $filters)) {
            $qb->andWhere('residencia.tipo = :tipo')
                ->setParameter('tipo', $filters['tipo']);
        }

        if (!empty($filters['query'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('UNACCENT(LOWER(usuario.nombre))',          'UNACCENT(LOWER(:query))'),
                $qb->expr()->like('UNACCENT(LOWER(usuario.apellidoPaterno))', 'UNACCENT(LOWER(:query))'),
                $qb->expr()->like('UNACCENT(LOWER(usuario.apellidoMaterno))', 'UNACCENT(LOWER(:query))'),
                $qb->expr()->like('UNACCENT(LOWER(residencia.especialidad))', 'UNACCENT(LOWER(:query))'),
                $qb->expr()->like('UNACCENT(LOWER(residencia.folio))',        'UNACCENT(LOWER(:query))'),
                $qb->expr()->like('UNACCENT(LOWER(residente.nacionalidad))',  'UNACCENT(LOWER(:query))')
            ))
                ->setParameter('query', '%' . $filters['query'] . '%');
        }

        $qbCount = clone $qb;
        $total   = (int) $qbCount->select('COUNT(residente)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($perPage > 0 && $page > 0) {
            $qb->setMaxResults($perPage)
                ->setFirstResult(($page - 1) * $perPage);
        }

        $data = $qb
            ->select('residente')
            ->addSelect('residencia.ciclo')
            ->addSelect('residencia.grado')
            ->addSelect('residencia.fechaInicio')
            ->addSelect('residencia.fechaTermino')
            ->orderBy('residencia.ciclo', 'DESC')
            ->addOrderBy('usuario.apellidoPaterno', 'ASC')
            ->addOrderBy('usuario.apellidoMaterno', 'ASC')
            ->addOrderBy('usuario.nombre', 'ASC')
            ->getQuery()
            ->getResult();

        return ['data' => $data, 'total' => $total];
    }
}
