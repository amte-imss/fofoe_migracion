<?php

namespace App\Repository;

use App\Entity\Delegacion;
use App\Entity\SolicitudInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DelegationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delegacion::class);
    }

    public function getAllDelegacionesNotNullRegion(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.region IS NOT NULL')
            ->andWhere('d.activo = TRUE')
            ->getQuery()
            ->getResult();
    }

    public function searchOneByNombre(string $nombre): ?Delegacion
    {
        return $this->createQueryBuilder('d')
            ->where('LOWER(unaccent(d.nombre)) LIKE LOWER(unaccent(:nombre))')
            ->setParameter('nombre', $nombre)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllDelsNoSelecs(int $id, mixed $usuario = null): array
    {
        $qb = $this->createQueryBuilder('delegacion')
            ->join('delegacion.unidades', 'unidad')
            ->join('unidad.camposClinicos', 'campo')
            ->join('campo.solicitud', 'solicitud')
            ->join('campo.convenio', 'convenio')
            ->leftJoin('solicitud.campus', 'campus')
            ->where('solicitud.estatus IN (:estatusSol)')
            ->andWhere('convenio.institucion = :idIns')
            ->andWhere('unidad.esUmae <> true')
            ->setParameter('idIns', $id)
            ->setParameter('estatusSol', [
                SolicitudInterface::CREADA,
                SolicitudInterface::REGISTRADA,
            ])
            ->groupBy('delegacion');

        if ($usuario && $usuario->getCampus() !== null) {
            $qb->andWhere('campus.id = :idCampus')
                ->setParameter('idCampus', $usuario->getCampus()->getId());
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllWithConvenio(int $institucionId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql  = '
            SELECT d.id, d.nombre
            FROM delegacion d
            LEFT JOIN convenio_delegacion cd ON d.id = cd.delegacion_id
            LEFT JOIN convenio c ON c.id = cd.convenio_id
            WHERE (c.todas_delegaciones OR cd.delegacion_id = d.id)
              AND c.cancelacion_anticipada = false
              AND c.institucion_id = :institucion
        ';

        $rows = $conn->executeQuery($sql, ['institucion' => $institucionId])
            ->fetchAllAssociative();

        if (empty($rows)) {
            return [];
        }

        return $this->createQueryBuilder('delegacion')
            ->where('delegacion.id IN (:ids)')
            ->setParameter('ids', array_column($rows, 'id'))
            ->getQuery()
            ->getResult();
    }
}
