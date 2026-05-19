<?php

namespace App\Repository\Enfermeria;

use App\Entity\Enfermeria\Solicitud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SolicitudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Solicitud::class);
    }

    public function findByYear(?string $year, ?int $escuelaId): array
    {
        $qb = $this->createQueryBuilder('solicitud')
            ->innerJoin('solicitud.unidad', 'escuela')
            ->leftJoin('solicitud.alumnos', 'alumnos')
            ->select('solicitud', 'COUNT(alumnos.id) as totalAlumnos')
            ->where('escuela.id = :escuela_id')
            ->andWhere('(solicitud.isTest = false OR solicitud.isTest IS NULL)')
            ->setParameter('escuela_id', $escuelaId)
            ->groupBy('solicitud.id')
            ->orderBy('solicitud.createdAt', 'DESC');

        if ($year) {
            $qb->andWhere('solicitud.fechaInicio >= :date')
                ->andWhere('solicitud.fechaInicio <= :date2')
                ->setParameter('date', "{$year}-01-01")
                ->setParameter('date2', "{$year}-12-31");
        }

        return $qb->getQuery()->getResult();
    }

    public function findByYearAndOOAD(?string $year, ?int $delegacionId): array
    {
        $qb = $this->createQueryBuilder('solicitud')
            ->innerJoin('solicitud.unidad', 'escuela')
            ->innerJoin('escuela.delegacion', 'delegacion')
            ->leftJoin('solicitud.alumnos', 'alumnos')
            ->select('solicitud', 'COUNT(alumnos.id) as totalAlumnos')
            ->where('delegacion.id = :delegacion_id')
            ->andWhere('(solicitud.isTest = false OR solicitud.isTest IS NULL)')
            ->setParameter('delegacion_id', $delegacionId)
            ->groupBy('solicitud.id')
            ->orderBy('solicitud.createdAt', 'DESC');

        if ($year) {
            $qb->andWhere('solicitud.fechaInicio >= :date')
                ->andWhere('solicitud.fechaInicio <= :date2')
                ->setParameter('date', "{$year}-01-01")
                ->setParameter('date2', "{$year}-12-31");
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllPaginated(int $page = 1, int $perPage = 15): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.alumnos', 'a')
            ->leftJoin('s.unidad', 'u')
            ->select('s', 'COUNT(a.id) as totalAlumnos', 'u')
            ->where('s.isTest = false OR s.isTest IS NULL')
            ->groupBy('s.id', 'u.id')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        return $qb->getQuery()->getResult();
    }
}
