<?php

namespace App\Repository;

use App\Entity\CoordinacionFirmante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoordinacionFirmante>
 */
class CoordinacionFirmanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoordinacionFirmante::class);
    }

    /**
     * Returns the non-deleted signing coordinations, keyed by id.
     */
    public function getCoordinacionesFirmantes(
        string $lookingForBy = '',
        string $orderDirection = 'DESC',
        string $orderBy = 'fecha_creacion',
    ): array {
        $sortField = $this->getClassMetadata()->hasField($orderBy) ? $orderBy : 'id';
        $sortDirection = strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC';

        $coordinations = $this->createListQueryBuilder($lookingForBy)
            ->orderBy('coordinacion_firmante.' . $sortField, $sortDirection)
            ->getQuery()
            ->getResult();

        $result = [];
        /** @var CoordinacionFirmante $coordination */
        foreach ($coordinations as $coordination) {
            $result[$coordination->getId()] = [
                'id' => $coordination->getId(),
                'nombre' => $coordination->getNombre(),
                'activa' => $coordination->getActiva(),
                'borrado' => $coordination->getBorrado(),
                'fechaCreacion' => $coordination->getFechaCreacion(),
                'fechaModificacion' => $coordination->getFechaModificacion(),
            ];
        }

        return $result;
    }

    /**
     * Returns the number of records listed by getCoordinacionesFirmantes().
     */
    public function getCoordinacionesFirmantesCount(string $lookingForBy = ''): int
    {
        return (int) $this->createListQueryBuilder($lookingForBy)
            ->select('COUNT(coordinacion_firmante.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the active signing coordinations matching the given name.
     *
     * @return CoordinacionFirmante[]
     */
    public function getCoordFirmantePorNombre(string $name): array
    {
        return $this->createQueryBuilder('coordinacion_firmante')
            ->where('LOWER(unaccent(coordinacion_firmante.nombre)) = LOWER(unaccent(:nombre))')
            ->andWhere('coordinacion_firmante.activa = true')
            ->andWhere('coordinacion_firmante.borrado = false')
            ->setParameter('nombre', $name)
            ->getQuery()
            ->getResult();
    }

    private function createListQueryBuilder(string $lookingForBy): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('coordinacion_firmante')
            ->where('coordinacion_firmante.borrado = false');

        if ($lookingForBy !== '') {
            $queryBuilder
                ->andWhere('LOWER(coordinacion_firmante.nombre) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($lookingForBy) . '%');
        }

        return $queryBuilder;
    }
}
