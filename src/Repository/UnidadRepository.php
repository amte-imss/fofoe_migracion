<?php

namespace App\Repository;

use App\Entity\SolicitudInterface;
use App\Entity\Unidad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UnidadRepository extends ServiceEntityRepository implements UnidadRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unidad::class);
    }

    public function getAllUnidadesByDelegacion(int $delegacion_id = 1, bool $include_umaes = true): array
    {
        $qb = $this->createQueryBuilder('unidad');
        $qb->innerJoin('unidad.delegacion', 'delegacion')
            ->where('delegacion.id = :delegacion_id')
            ->andWhere('unidad.activo = true')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNotNull('unidad.tipoUnidad'),
                $qb->expr()->eq('unidad.esUmae', 'true')
            ))
            ->setParameter('delegacion_id', $delegacion_id)
            ->orderBy('unidad.nombre');

        if (!$include_umaes) {
            $qb->andWhere('unidad.esUmae = false');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByClaveDepartamental(string $clave): ?Unidad
    {
        return $this->createQueryBuilder('unidad')
            ->innerJoin('unidad.departamentos', 'departamento')
            ->where('departamento.claveDepartamental = :clave')
            ->setParameter('clave', $clave)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllUMAEs(): array
    {
        return $this->createQueryBuilder('unidad')
            ->where('unidad.activo = true')
            ->andWhere('unidad.esUmae = true')
            ->getQuery()
            ->getResult();
    }

    public function getAllUMAEsNoSelecs(int $id, mixed $usuario = null): array
    {
        $qb = $this->createQueryBuilder('unidad')
            ->join('unidad.camposClinicos', 'campo')
            ->join('campo.solicitud', 'solicitud')
            ->join('campo.convenio', 'convenio')
            ->leftJoin('solicitud.campus', 'campus')
            ->andWhere('unidad.esUmae = true')
            ->andWhere('solicitud.estatus IN (:estatusSol)')
            ->andWhere('convenio.institucion = :idIns')
            ->groupBy('unidad')
            ->setParameter('idIns', $id)
            ->setParameter('estatusSol', [SolicitudInterface::CREADA]);

        if ($usuario && $usuario->getCampus() !== null) {
            $qb->andWhere('campus.id = :idCampus')
                ->setParameter('idCampus', $usuario->getCampus()->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
