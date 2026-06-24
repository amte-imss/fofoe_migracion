<?php

namespace App\Repository;

use App\Entity\Pago;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PagoRepository extends ServiceEntityRepository implements PagoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pago::class);
    }

    public function getAllPagosByRequest(int $id): array
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.solicitud = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function getComprobante(string $referenciaBancaria): mixed
    {
        return $this->findOneBy(['referenciaBancaria' => $referenciaBancaria]);
    }

    public function save(Pago $pago): void
    {
        $this->_em->persist($pago);
        $this->_em->flush();
    }

    public static function createGetPagoByReferenciaBancariaCriteria(string $referenciaBancaria): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('referenciaBancaria', $referenciaBancaria));
    }

    public function getPagosCampoClinicosBySolicitud(int $solicitud_id): array
    {
        return $this->createQueryBuilder('pago')
            ->innerJoin('pago.solicitud', 'solicitud')
            ->innerJoin('solicitud.camposClinicos', 'campos_clinicos')
            ->where('solicitud.id = :solicitud_id')
            ->andWhere('pago.referenciaBancaria = campos_clinicos.referenciaBancaria')
            ->setParameter('solicitud_id', $solicitud_id)
            ->getQuery()
            ->getResult();
    }

    public function paginatePagos(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $year    = Carbon::now()->format('Y');
        $qb      = $this->createPagosQueryBuilder($filters);

        $total = (int) (clone $qb)
            ->select('count(pago.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $nuevos = (int) (clone $qb)
            ->select('COUNT(pago.id)')
            ->andWhere('pago.fechaPagoRegistrada >= :fecha_i AND pago.fechaPagoRegistrada <= :fecha_f')
            ->setParameter('fecha_i', "{$year}-01-01")
            ->setParameter('fecha_f', "{$year}-12-31")
            ->getQuery()
            ->getSingleScalarResult();

        $validados = (int) (clone $qb)
            ->select('count(pago.id)')
            ->andWhere('pago.validado = true')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $this->getApiPagos($filters, $page, $perPage)
            ->orderBy('pago.id')
            ->getQuery()
            ->getResult();

        $result = [
            'data'      => $data,
            'total'     => $total,
            'nuevos'    => $nuevos,
            'validados' => $validados,
        ];

        if (empty($filters)) {
            $result['referencia']   = array_merge(['Todos'], (clone $qb)->select('pago.referenciaBancaria')->distinct(true)->getQuery()->getResult());
            $result['ooad']         = array_merge(['Todos'], (clone $qb)->select('posgrado_residencia.delegacion')->distinct(true)->getQuery()->getResult());
            $result['especialidad'] = array_merge(['Todos'], (clone $qb)->select('posgrado_residencia.especialidad')->distinct(true)->getQuery()->getResult());
            $result['sede']         = array_merge(['Todos'], (clone $qb)->select('posgrado_residencia.sede')->distinct(true)->getQuery()->getResult());
        }

        return $result;
    }

    public function getApiPagos(array $filters, ?int $page = null, ?int $perPage = null): QueryBuilder
    {
        $qb = $this->createPagosQueryBuilder($filters);

        if (!is_null($page) && !is_null($perPage)) {
            $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return $qb;
    }

    public function createPagosQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('pago')
            ->innerJoin('pago.solicitud', 'solicitud')
            ->leftJoin('pago.residencia', 'posgrado_residencia')
            ->leftJoin('posgrado_residencia.residente', 'residente')
            ->leftJoin('residente.usuario', 'usuario')
            ->where('pago.isTest = false');

        if (!empty($filters['ooad'])) {
            $qb->andWhere('upper(unaccent(posgrado_residencia.delegacion)) like UPPER(unaccent(:ooad))')
                ->setParameter('ooad', '%' . $filters['ooad'] . '%');
        }
        if (!empty($filters['sede'])) {
            $qb->andWhere('upper(unaccent(posgrado_residencia.sede)) like UPPER(unaccent(:sede))')
                ->setParameter('sede', '%' . $filters['sede'] . '%');
        }
        if (!empty($filters['referencia'])) {
            $qb->andWhere('upper(unaccent(pago.referenciaBancaria)) like UPPER(unaccent(:referencia))')
                ->setParameter('referencia', '%' . $filters['referencia'] . '%');
        }

        return $qb;
    }

    public function paginate(int $perPage = 10, int $offset = 1, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('pago')
            ->select(['pago', 'solicitud'])
            ->join('pago.solicitud', 'solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->join('convenio.delegacion', 'delegacion')
            ->leftJoin('pago.factura', 'factura');

        foreach ([
                     'institucion'  => 'institucion.nombre',
                     'delegacion'   => 'delegacion.nombre',
                     'referencia'   => 'pago.referenciaBancaria',
                     'factura'      => 'factura.folio',
                     'no_solicitud' => 'solicitud.noSolicitud',
                 ] as $filter => $field) {
            if (!empty($filters[$filter])) {
                $qb->andWhere("upper(unaccent({$field})) like UPPER(unaccent(:{$filter}))")
                    ->setParameter($filter, '%' . $filters[$filter] . '%');
            }
        }

        if (!empty($filters['monto']) && is_numeric($filters['monto'])) {
            $qb->andWhere("concat(pago.monto,'') like :monto")
                ->setParameter('monto', '%' . $filters['monto'] . '%');
        }

        if (!empty($filters['estado'])) {
            match ($filters['estado']) {
                'a' => $qb->andWhere('pago.validado is null'),
                'b' => $qb->andWhere('pago.validado = true'),
                'c' => $qb->andWhere('pago.validado = true AND pago.requiereFactura = true AND pago.factura is NULL'),
                'd' => $qb->andWhere('pago.validado = false'),
                default => null,
            };
        }

        $year    = $filters['year'] ?? Carbon::now()->format('Y');
        $qb->andWhere('pago.fechaPago >= :fecha_i AND pago.fechaPago <= :fecha_f')
            ->setParameter('fecha_i', "{$year}-01-01")
            ->setParameter('fecha_f', "{$year}-12-31");

        $qbCount = clone $qb;

        match ($filters['orderby'] ?? null) {
            'a'     => $qb->orderBy('pago.fechaPago', 'DESC'),
            'b'     => $qb->orderBy('pago.fechaPago', 'ASC'),
            'c'     => $qb->orderBy('solicitud.noSolicitud', 'DESC'),
            'd'     => $qb->orderBy('solicitud.noSolicitud', 'ASC'),
            default => $qb->orderBy('pago.id', 'DESC'),
        };

        return [
            'data'  => $qb->distinct()
                ->setFirstResult(($offset - 1) * $perPage)
                ->setMaxResults($perPage)
                ->getQuery()
                ->getResult(),
            'total' => (int) $qbCount->select('COUNT(distinct pago.id)')->getQuery()->getSingleScalarResult(),
        ];
    }

    public function getComprobantesPagoByReferenciaBancaria(string $referenciaBancaria): array
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.referenciaBancaria = :referenciaBancaria')
            ->setParameter('referenciaBancaria', $referenciaBancaria)
            ->getQuery()
            ->getResult();
    }

    public static function getUltimoPagoByCriteria(string $referenciaBancaria): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('referenciaBancaria', $referenciaBancaria))
            ->orderBy(['id' => Criteria::DESC])
            ->setFirstResult(0)
            ->setMaxResults(1);
    }

    public static function getPagosByReferenciaBancaria(string $referenciaBancaria): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('referenciaBancaria', $referenciaBancaria));
    }

    public static function getPagosCargadosByReferenciaBancaria(string $referenciaBancaria): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('referenciaBancaria', $referenciaBancaria))
            ->andWhere(Criteria::expr()->neq('comprobantePago', null));
    }

    public function getReporteIngresosMes(int $anio): array
    {
        $selAnio = "to_char(pago.fechaPago, 'YYYY')";
        $selMes  = "to_char(pago.fechaPago, 'MM')";

        $qb = $this->createQueryBuilder('pago')
            ->select([
                "{$selAnio} as Anio",
                "{$selMes} as Mes",
                "sum(case when pago.validado = 'TRUE' and campos_clinicos.cicloAcademico = 1 then campos_clinicos.monto else 0 end) as ccVal",
                "sum(case when pago.validado IS NULL and campos_clinicos.cicloAcademico = 1 then campos_clinicos.monto else 0 end) as ccPend",
                "sum(case when pago.validado = 'TRUE' and campos_clinicos.cicloAcademico = 2 then campos_clinicos.monto else 0 end) as intVal",
                "sum(case when pago.validado IS NULL and campos_clinicos.cicloAcademico = 2 then campos_clinicos.monto else 0 end) as intPend",
                "sum(case when pago.validado = 'TRUE' and pago.escuelaEnfermeriaSolicitud is not null then pago.monto else 0 end) as enfVal",
                "sum(case when pago.validado IS NULL and pago.escuelaEnfermeriaSolicitud is not null then pago.monto else 0 end) as enfPend",
                "sum(case when pago.validado = 'TRUE' and pago.residencia is not null then pago.monto else 0 end) as extVal",
                "sum(case when pago.validado IS NULL and pago.residencia is not null then pago.monto else 0 end) as extPend",
                "sum(case when pago.validado = 'TRUE' and eduPerSol.tipoSolicitud is not null and (eduPerTipo.code = 'modalidad-presencial' or eduPerTipo.code = 'presencial') then pago.monto else 0 end) as presVal",
                "sum(case when pago.validado IS NULL and eduPerSol.tipoSolicitud is not null and (eduPerTipo.code = 'modalidad-presencial' or eduPerTipo.code = 'presencial') then pago.monto else 0 end) as presPend",
                "sum(case when pago.validado = 'TRUE' and eduPerSol.tipoSolicitud is not null and eduPerTipo.code = 'simulacion' then pago.monto else 0 end) as simVal",
                "sum(case when pago.validado IS NULL and eduPerSol.tipoSolicitud is not null and eduPerTipo.code = 'simulacion' then pago.monto else 0 end) as simPend",
                "sum(case when pago.validado = 'TRUE' and eduPerSol.tipoSolicitud is not null and (eduPerTipo.code = 'distancia' or eduPerTipo.code = 'a-distancia') then pago.monto else 0 end) as distVal",
                "sum(case when pago.validado IS NULL and eduPerSol.tipoSolicitud is not null and (eduPerTipo.code = 'distancia' or eduPerTipo.code = 'a-distancia') then pago.monto else 0 end) as distPend",
            ])
            ->innerJoin('pago.solicitud', 'solicitud')
            ->innerJoin('solicitud.camposClinicos', 'campos_clinicos')
            ->innerJoin('campos_clinicos.convenio', 'convenio')
            ->leftJoin('pago.eduPerSolicitud', 'eduPerSol')
            ->leftJoin('eduPerSol.tipoSolicitud', 'eduPerTipo')
            ->where($qb->expr()->orX(
                '(pago.referenciaBancaria = solicitud.referenciaBancaria and pago.solicitudId = solicitud.id)',
                '(pago.referenciaBancaria = campos_clinicos.referenciaBancaria and pago.solicitudId = campos_clinicos.solicitudId)',
                'pago.escuelaEnfermeriaSolicitud is not null',
                'pago.residencia is not null',
                'pago.eduPerSolicitud is not null'
            ))
            ->andWhere("{$selAnio} = :anio")
            ->setParameter('anio', $anio)
            ->andWhere('pago.isTest = false')
            ->groupBy('Anio, Mes')
            ->orderBy('Anio', 'DESC')
            ->addOrderBy('Mes', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getAllPagosByInstitucion(int $id): array
    {
        return $this->createQueryBuilder('pago')
            ->innerJoin('pago.solicitud', 'solicitud')
            ->innerJoin('solicitud.camposClinicos', 'campos_clinicos')
            ->where('solicitud.id = :solicitud_id')
            ->andWhere('pago.referenciaBancaria = campos_clinicos.referenciaBancaria')
            ->setParameter('solicitud_id', $id)
            ->getQuery()
            ->getResult();
    }

    public function getComprobantesPagoValidadosByReferenciaBancaria(string $referenciaBancaria, int $solicitudId): array
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.referenciaBancaria = :referenciaBancaria')
            ->andWhere('pago.validado IS NOT NULL')
            ->andWhere('pago.solicitud = :solicitudId')
            ->setParameter('solicitudId', $solicitudId)
            ->setParameter('referenciaBancaria', $referenciaBancaria)
            ->getQuery()
            ->getResult();
    }

    public function findEscuelaEnfermeriaByStatus(string $status, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('pago')
            ->where('pago.escuelaEnfermeriaSolicitudId is not null')
            ->andWhere('pago.fechaPago IS NOT NULL')
            ->andWhere('(pago.isTest is not null and pago.isTest = false)');

        match ($status) {
            'wait_validation'  => $qb->andWhere('pago.validado IS NULL'),
            'validated'        => $qb->andWhere('((pago.validado = true AND pago.requiereFactura = false) OR (pago.validado = true AND pago.requiereFactura = true AND pago.facturaGenerada = true))'),
            'rejected'         => $qb->andWhere('pago.validado = false'),
            'invoice_pending'  => $qb->andWhere('pago.validado = true AND pago.requiereFactura = true AND (pago.facturaGenerada = false OR pago.facturaGenerada is null)'),
            default            => $qb->andWhere('pago.id < 0'),
        };

        if (!empty($filters['name'])) {
            $qb->innerJoin('pago.escuelaEnfermeriaSolicitud', 'alumno')
                ->andWhere('upper(alumno.nombre) like :name')
                ->setParameter('name', '%' . mb_strtoupper($filters['name']) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findEduPerByStatus(string $status, string $type): array
    {
        $qb = $this->createQueryBuilder('pago')
            ->join('pago.eduPerSolicitud', 'eduPerSolicitud')
            ->join('eduPerSolicitud.tipoSolicitud', 'tipo')
            ->where('pago.eduPerSolicitudId is not null')
            ->andWhere('tipo.code = :type')
            ->setParameter('type', $type);

        match ($status) {
            'wait_validation'  => $qb->andWhere('pago.validado IS NULL'),
            'validated'        => $qb->andWhere('((pago.validado = true AND pago.requiereFactura = false) OR (pago.validado = true AND pago.requiereFactura = true AND pago.facturaGenerada = true))'),
            'rejected'         => $qb->andWhere('pago.validado = false'),
            'invoice_pending'  => $qb->andWhere('pago.validado = true AND pago.requiereFactura = true AND (pago.facturaGenerada = false OR pago.facturaGenerada is null)'),
            default            => $qb->andWhere('pago.id < 0'),
        };

        return $qb->getQuery()->getResult();
    }

    public function getTotalPagosByYear(int $year): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.fechaCreacion >= :date1 or p.fechaCreacion <= :date2')
            ->setParameter('date1', $year . '-01-01')
            ->setParameter('date2', $year . '-12-31')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPagoPendienteByEscuelaEnfermeria(int $escuelaEnfermeriaId): ?Pago
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.escuelaEnfermeriaSolicitudId = :escuelaEnfermeriaId')
            ->andWhere('pago.fechaPago IS NULL')
            ->setParameter('escuelaEnfermeriaId', $escuelaEnfermeriaId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPagoPendienteByEduPerm(int $eduPerSolicitudId): ?Pago
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.eduPerSolicitudId = :eduPerSolicitudId')
            ->andWhere('pago.fechaPago IS NULL')
            ->setParameter('eduPerSolicitudId', $eduPerSolicitudId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
