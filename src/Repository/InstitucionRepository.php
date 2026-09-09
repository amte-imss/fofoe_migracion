<?php

namespace App\Repository;

use App\Entity\Convenio;
use App\Entity\Institucion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class InstitucionRepository extends ServiceEntityRepository implements InstitucionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Institucion::class);
    }

    public function findAllPrivate(?int $delegacion_id = null): array
    {
        $qb = $this->createQueryBuilder('institucion')
            ->innerJoin('institucion.convenios', 'convenio')
            ->innerJoin('convenio.carrera', 'carrera')
            ->innerJoin('convenio.cicloAcademico', 'ciclo')
            ->innerJoin('carrera.nivelAcademico', 'nivelAcademico')
            ->where('convenio.sector = :private')
            ->andWhere('ciclo.activo = true')
            ->setParameter('private', 'Privado');

        if ($delegacion_id) {
            $qb->andWhere('convenio.delegacion = :delegacion_id')
                ->setParameter('delegacion_id', $delegacion_id);
        }

        return $qb->orderBy('institucion.nombre')->getQuery()->getResult();
    }

    public function getInstitucionBySolicitudId(int $id): ?Institucion
    {
        try {
            return $this->createQueryBuilder('institucion')
                ->join('institucion.convenios', 'convenios')
                ->join('convenios.camposClinicos', 'camposClinicos')
                ->join('camposClinicos.solicitud', 'solicitud')
                ->where('solicitud.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function searchOneByNombre(string $nombre): ?Institucion
    {
        return $this->createQueryBuilder('institucion')
            ->where('LOWER(unaccent(institucion.nombre)) LIKE LOWER(unaccent(:nombre))')
            ->setParameter('nombre', $nombre)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInstitucionByPagoId(int $id): ?Institucion
    {
        return $this->createQueryBuilder('institucion')
            ->join('institucion.convenios', 'convenios')
            ->join('convenios.camposClinicos', 'camposClinicos')
            ->join('camposClinicos.solicitud', 'solicitud')
            ->join('solicitud.pagos', 'pagos')
            ->where('pagos.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function searchByNombre(string $nombre): array
    {
        return $this->createQueryBuilder('institucion')
            ->where("LOWER(unaccent(institucion.nombre)) LIKE CONCAT(LOWER(unaccent(:nombre)), '%')")
            ->setParameter('nombre', $nombre)
            ->orderBy('institucion.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchByTipoAndNombre(string $tipo, string $nombre): array
    {
        $qb = $this->createQueryBuilder('institucion')
            ->leftJoin('institucion.convenios', 'convenios')
            ->where("LOWER(unaccent(institucion.nombre)) LIKE CONCAT(LOWER(unaccent(:nombre)), '%')")
            ->setParameter('nombre', $nombre);

        if ($tipo == 1) {
            $qb->andWhere('convenios.id is not null');
        } else {
            $qb->andWhere('convenios.id is null');
        }

        return $qb->orderBy('institucion.nombre', 'ASC')->getQuery()->getResult();
    }

    public function getInstitucionPorRFC(string $rfcInstitucion, bool $esForm = false, ?int $idInstitucion = null): array
    {
        $instituciones = $this->createQueryBuilder('institucion')
            ->where('institucion.rfc = :rfc')
            ->setParameter('rfc', $rfcInstitucion)
            ->getQuery()
            ->getResult();

        if (!$esForm) {
            return $instituciones;
        }

        $result = [];
        foreach ($instituciones as $objInstitucion) {
            if (!is_null($idInstitucion) && $idInstitucion == $objInstitucion->getId()) {
                return [$objInstitucion->getNombre() => $objInstitucion->getId()];
            }
            $result[$objInstitucion->getNombre()] = $objInstitucion->getId();
        }

        return $result;
    }

    public function getInstitutionById(int $idInstitucion): ?Institucion
    {
        return $this->createQueryBuilder('institucion')
            ->andWhere('institucion.id = :idInstitucion')
            ->setParameter('idInstitucion', $idInstitucion)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginateAdminInstituciones(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $total = (clone $this->createAdminQueryBuilder($filters))
            ->select('count(institucion.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $this->createAdminQueryBuilder($filters)
            ->orderBy('institucion.razonSocial')
            ->setFirstResult(!is_null($page) && !is_null($perPage) ? ($page - 1) * $perPage : 0)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'data'    => $data,
            'perPage' => $perPage,
            'page'    => $page,
            'total'   => $total,
        ];
    }

    // Métodos no declarados en la interfaz — solo uso interno o legacy
    public function getInstitutionByName(string $nombre): array
    {
        return $this->createQueryBuilder('institucion')
            ->where("LOWER(unaccent(institucion.nombre)) LIKE CONCAT(LOWER(unaccent(:nombre)), '%')")
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getResult();
    }

    public function getInstitucionPorRFCV2(string $rfcInstitucion): array
    {
        return $this->createQueryBuilder('institucion')
            ->where('institucion.rfc = :rfc')
            ->setParameter('rfc', $rfcInstitucion)
            ->getQuery()
            ->getResult();
    }

    private function createAdminQueryBuilder(array $filters): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('institucion')
            ->leftJoin('institucion.delegacion', 'delegacion')
            ->where('1 = 1');

        if (!empty($filters['came_delegacion'])) {
            $sub = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(Convenio::class, 'c')
                ->where('(c.todasDelegaciones = true AND c.institucion = institucion)')
                ->orWhere('(c.institucion = institucion AND IDENTITY(c.delegacion) IN (:cameDelegacion))');

            $qb->where($qb->expr()->orX(
                $qb->expr()->in('IDENTITY(institucion.delegacion)', ':cameDelegacion'),
                $qb->expr()->exists($sub->getDQL())
            ))->setParameter('cameDelegacion', $filters['came_delegacion']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $qb->andWhere(
                'upper(unaccent(institucion.rfc)) like upper(unaccent(concat(\'%\', :rfc, \'%\'))) ' .
                'OR upper(unaccent(institucion.razonSocial)) like upper(unaccent(concat(\'%\', :razon_social, \'%\'))) ' .
                'OR upper(unaccent(institucion.nombre)) like upper(unaccent(concat(\'%\', :nombre, \'%\')))'
            )
                ->setParameter('rfc', $search)
                ->setParameter('razon_social', $search)
                ->setParameter('nombre', $search);
        }

        return $qb;
    }
}
