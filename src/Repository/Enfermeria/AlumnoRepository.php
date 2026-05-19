<?php

namespace App\Repository\Enfermeria;

use App\Entity\Enfermeria\Alumno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AlumnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alumno::class);
    }

    public function findBySolicitud(int $solicitudId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.solicitud = :sid')
            ->setParameter('sid', $solicitudId)
            ->orderBy('a.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Listado filtrable de alumnos con datos de solicitud y unidad.
     */
    public function findByFilters(
        ?string $status,
        ?int    $solicitudId,
        ?int    $unidadId,
        ?int    $year,
        int     $page    = 1,
        int     $perPage = 20
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.solicitud', 's')
            ->leftJoin('s.unidad', 'u')
            ->addSelect('s', 'u');

        if ($status !== null && $status !== '') {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }
        if ($solicitudId) {
            $qb->andWhere('a.solicitud = :solicitudId')
               ->setParameter('solicitudId', $solicitudId);
        }
        if ($unidadId) {
            $qb->andWhere('s.unidad = :unidadId')
               ->setParameter('unidadId', $unidadId);
        }
        if ($year) {
            $qb->andWhere('s.fechaInicio >= :yearStart AND s.fechaInicio < :yearEnd')
               ->setParameter('yearStart', new \DateTime("{$year}-01-01"))
               ->setParameter('yearEnd',   new \DateTime(($year + 1) . '-01-01'));
        }

        $total = (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        $results = $qb
            ->orderBy('a.nombre', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return ['results' => $results, 'total' => (int) $total];
    }

    /**
     * Cantidad de alumnos agrupada por status.
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) AS total')
            ->groupBy('a.status')
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['status'] ?? ''] = (int) $row['total'];
        }
        return $map;
    }

    /**
     * Alumnos de una escuela (unidad) en un año dado.
     */
    public function findByUnidadAndYear(int $unidadId, int $year): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.solicitud', 's')
            ->andWhere('s.unidad = :unidadId')
            ->andWhere('s.fechaInicio >= :yearStart AND s.fechaInicio < :yearEnd')
            ->setParameter('unidadId',  $unidadId)
            ->setParameter('yearStart', new \DateTime("{$year}-01-01"))
            ->setParameter('yearEnd',   new \DateTime(($year + 1) . '-01-01'))
            ->orderBy('a.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

