<?php

namespace App\Repository;

use App\Entity\CampoClinico;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class CampoClinicoRepository extends ServiceEntityRepository implements CampoClinicoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampoClinico::class);
    }

    public function getAllCamposClinicosByInstitucion(int $id): array
    {
        return $this->createQueryBuilder('campo_clinico')
            ->join('campo_clinico.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->where('institucion.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function getAllCamposClinicosByRequest(int $id, ?string $search = null, bool $autorizados = false): array
    {
        try {
            $qb = $this->createQueryBuilder('campo_clinico')
                ->where('campo_clinico.solicitud = :id')
                ->setParameter('id', $id);

            if ($search !== null && $search !== '') {
                $qb->andWhere('LOWER(campo_clinico.promocion) LIKE LOWER(:search)')
                    ->setParameter('search', '%' . $search . '%');
            }

            if ($autorizados) {
                $qb->andWhere('campo_clinico.lugaresAutorizados <> 0');
            }

            return $qb->getQuery()->getResult();
        } catch (NoResultException|NonUniqueResultException) {
        }

        return [];
    }

    public function getTotalSolicitudesByInstitucion(int $id): int
    {
        try {
            return (int)$this->createQueryBuilder('campo_clinico')
                ->select('count(campo_clinico.id)')
                ->join('campo_clinico.convenio', 'convenio')
                ->join('convenio.institucion', 'institucion')
                ->join('campo_clinico.solicitud', 'solicitud')
                ->where('institucion.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
        }

        return 0;
    }

    public function getDistinctCarrerasBySolicitud(int $id): array
    {
        try {
            $conn = $this->getEntityManager()->getConnection();
            $stmt = $conn->prepare('
                SELECT carreras_unicas.*,
                       monto_carrera.monto_colegiatura,
                       monto_carrera.monto_inscripcion
                FROM (
                    SELECT DISTINCT carrera.id             AS id,
                                    carrera.nombre         AS nombre,
                                    nivel_academico.nombre AS nivel_academico,
                                    campo_clinico.id       AS campo_clinico_id,
                                    campo_clinico.observaciones AS observaciones
                    FROM campo_clinico
                    JOIN convenio ON campo_clinico.convenio_id = convenio.id
                    JOIN carrera ON convenio.carrera_id = carrera.id
                    JOIN nivel_academico ON carrera.nivel_academico_id = nivel_academico.id
                    JOIN solicitud ON campo_clinico.solicitud_id = solicitud.id
                    WHERE campo_clinico.lugares_autorizados <> 0
                      AND campo_clinico.solicitud_id = :id
                ) AS carreras_unicas
                LEFT JOIN monto_carrera ON carreras_unicas.id = monto_carrera.carrera_id
                    AND monto_carrera.campo_clinico_id = carreras_unicas.campo_clinico_id
                ORDER BY campo_clinico_id ASC
            ');

            return $stmt->executeQuery(['id' => $id])->fetchAllAssociative();
        } catch (DBALException) {
        }

        return [];
    }

    public function getAllCamposByPage(array $filtros): array
    {
        $query = $this->createQueryBuilder('campo_clinico')
            ->join('campo_clinico.convenio', 'convenio')
            ->join('campo_clinico.solicitud', 'solicitud')
            ->join('convenio.institucion', 'institucion')
            ->join('campo_clinico.unidad', 'unidad')
            ->orderBy('solicitud.id', 'ASC')
            ->andWhere('solicitud.isTest = FALSE');

        $query = $this->procesarFiltros($query, $filtros)->getQuery();

        [$paginator, $page, $pageSize, $pagesCount, $totalItems] = $this->setPaginador($query, $filtros);

        if (!empty($filtros['export'])) {
            $campos = $paginator->getQuery()->getResult();
        } else {
            $campos = $paginator->getQuery()
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getResult();
        }

        return [$campos, $totalItems, $pagesCount, $pageSize];
    }

    public function getAllCamposClinicos(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        if (empty($filters)) {
            $qb = $this->apiCamposQueryBuilder([]);
            $total = (int)$qb->select('count(campo_clinico.id)')->getQuery()->getSingleScalarResult();

            $activos = (int)$this->apiCamposQueryBuilder([])
                ->select('COUNT(campo_clinico.id)')
                ->andWhere('campo_clinico.fechaFinal >= :fecha')
                ->setParameter('fecha', Carbon::now())
                ->getQuery()->getSingleScalarResult();

            $year = Carbon::now()->format('Y');
            $nuevos = (int)$this->apiCamposQueryBuilder([])
                ->select('COUNT(campo_clinico.id)')
                ->andWhere('campo_clinico.fechaInicial >= :fecha_i AND campo_clinico.fechaInicial <= :fecha_f')
                ->setParameter('fecha_i', "{$year}-01-01")
                ->setParameter('fecha_f', "{$year}-12-31")
                ->getQuery()->getSingleScalarResult();

            $cicloAcademico = $this->apiCamposQueryBuilder([])
                ->select('cicloAcademico.id', 'cicloAcademico.nombre')
                ->distinct(true)->orderBy('cicloAcademico.id', 'ASC')
                ->getQuery()->getResult();
            array_unshift($cicloAcademico, 'todos');

            $institucion = $this->apiCamposQueryBuilder([])
                ->select('institucion.id', 'institucion.nombre')
                ->distinct(true)->orderBy('institucion.id', 'ASC')
                ->getQuery()->getResult();

            $convenio = $this->apiCamposQueryBuilder([])
                ->select('convenio.id', 'convenio.nombre')
                ->distinct(true)->orderBy('convenio.id', 'ASC')
                ->getQuery()->getResult();

            $ooad = $this->apiCamposQueryBuilder([])
                ->select('delegacionInstitucion.id', 'delegacionInstitucion.nombre')
                ->distinct(true)->orderBy('delegacionInstitucion.id', 'ASC')
                ->getQuery()->getResult();

            $data = $this->getApiCampos($filters, $page, $perPage)
                ->orderBy('campo_clinico.id')->getQuery()->getResult();

            return [
                'total' => $total,
                'activos' => $activos,
                'nuevos' => $nuevos,
                'vencidos' => $total - $activos,
                'Ciclos_academicos' => $cicloAcademico,
                'convenio' => $convenio,
                'institucion' => $institucion,
                'ooad' => $ooad,
                'data' => $data,
            ];
        }

        return [
            'data' => $this->getApiCampos($filters, null, null)
                ->orderBy('campo_clinico.id')->getQuery()->getResult(),
        ];
    }

    public function getApiCampos(array $filters, ?int $page = null, ?int $perPage = null): QueryBuilder
    {
        $qb = $this->apiCamposQueryBuilder($filters);

        if (!is_null($page) && !is_null($perPage)) {
            $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return $qb;
    }

    public function apiCamposQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('campo_clinico')
            ->join('campo_clinico.convenio', 'convenio')
            ->join('campo_clinico.cicloAcademico', 'cicloAcademico')
            ->join('campo_clinico.solicitud', 'solicitud')
            ->join('convenio.institucion', 'institucion')
            ->join('campo_clinico.unidad', 'unidad')
            ->leftJoin('institucion.delegacion', 'delegacionInstitucion')
            ->where('solicitud.isTest = false');

        if (!empty($filters['institucion'])) {
            $qb->andWhere('institucion.id = :institucion')->setParameter('institucion', $filters['institucion']);
        }
        if (!empty($filters['convenio'])) {
            $qb->andWhere('convenio.id = :convenio')->setParameter('convenio', $filters['convenio']);
        }
        if (!empty($filters['ooad'])) {
            $qb->andWhere('delegacionInstitucion.id = :ooad')->setParameter('ooad', $filters['ooad']);
        }
        if (!empty($filters['cicloEducativo'])) {
            $qb->andWhere('cicloAcademico.id = :cicloEducativo')->setParameter('cicloEducativo', $filters['cicloEducativo']);
        }

        return $qb;
    }

    public function getAllSolicitudes(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $camposClinicos = $this->apiSolicitudQueryBuilder($filters)->getQuery()->getResult();

        $idsSolicitudes = array_unique(array_map(
            fn($campo) => $campo->getSolicitud()->getId(),
            $camposClinicos
        ));
        $total = count($idsSolicitudes);

        $solicitudesUnicas = [];
        foreach ($camposClinicos as $campo) {
            $solicitud = $campo->getSolicitud();
            if ($solicitud) {
                $solicitudesUnicas[$solicitud->getId()] = $solicitud;
            }
        }

        $anioActual = Carbon::now()->format('Y');
        $total_nuevas = count(array_filter($solicitudesUnicas, function ($solicitud) use ($anioActual) {
            $fecha = $solicitud->getFecha();
            if (!$fecha) return false;
            return $fecha instanceof \DateTimeInterface
                ? $fecha->format('Y') === $anioActual
                : substr($fecha, -4) === $anioActual;
        }));

        $total_pagadas = count(array_filter($solicitudesUnicas, function ($solicitud) {
            return trim($solicitud?->getEstatusIEFormatted() ?? '') === 'Solicitud Pagada';
        }));

        if (empty($filters)) {
            $cicloAcademico = $this->apiSolicitudQueryBuilder([])
                ->select('cicloAcademico.id', 'cicloAcademico.nombre')
                ->distinct(true)->orderBy('cicloAcademico.id', 'ASC')
                ->getQuery()->getResult();
            array_unshift($cicloAcademico, 'Todos');

            $institucion = $this->apiSolicitudQueryBuilder([])
                ->select('institucion.id', 'institucion.nombre')
                ->distinct(true)->orderBy('institucion.id', 'ASC')
                ->getQuery()->getResult();
            array_unshift($institucion, 'Todos');

            $ooad = $this->apiSolicitudQueryBuilder([])
                ->select('delegacionInstitucion.id', 'delegacionInstitucion.nombre')
                ->distinct(true)->orderBy('delegacionInstitucion.id', 'ASC')
                ->getQuery()->getResult();
            array_unshift($ooad, 'Todos');

            return [
                'total' => $total,
                'pagadas' => $total_pagadas,
                'nuevos' => $total_nuevas,
                'Ciclos_academicos' => $cicloAcademico,
                'institucion' => $institucion,
                'ooad' => $ooad,
                'data' => $this->getApiSolicitudes($filters, $page, $perPage)
                    ->orderBy('solicitud.id')->getQuery()->getResult(),
            ];
        }

        return [
            'total' => $total,
            'pagadas' => $total_pagadas,
            'nuevos' => $total_nuevas,
            'data' => $this->getApiSolicitudes($filters, null, null)
                ->orderBy('solicitud.id')->getQuery()->getResult(),
        ];
    }

    public function getApiSolicitudes(array $filters, ?int $page = null, ?int $perPage = null): QueryBuilder
    {
        $qb = $this->apiSolicitudQueryBuilder($filters);

        if (!is_null($page) && !is_null($perPage)) {
            $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return $qb;
    }

    public function apiSolicitudQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('campo_clinico')
            ->join('campo_clinico.solicitud', 'solicitud')
            ->join('campo_clinico.convenio', 'convenio')
            ->join('campo_clinico.cicloAcademico', 'cicloAcademico')
            ->join('convenio.institucion', 'institucion')
            ->join('campo_clinico.unidad', 'unidad')
            ->leftJoin('institucion.delegacion', 'delegacionInstitucion')
            ->where('solicitud.isTest = false');

        if (!empty($filters['institucion'])) {
            $qb->andWhere('institucion.id = :institucion')->setParameter('institucion', $filters['institucion']);
        }
        if (!empty($filters['ooad'])) {
            $qb->andWhere('delegacionInstitucion.id = :ooad')->setParameter('ooad', $filters['ooad']);
        }
        if (!empty($filters['cicloEducativo'])) {
            $qb->andWhere('cicloAcademico.id = :cicloEducativo')->setParameter('cicloEducativo', $filters['cicloEducativo']);
        }

        return $qb;
    }

    public function getAllCamposClinicosBySolicitud(int $id, int $perPage = 10, int $page = 1, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('campoClinico')
            ->innerJoin('campoClinico.solicitud', 'solicitud')
            ->innerJoin('campoClinico.unidad', 'unidad')
            ->innerJoin('campoClinico.convenio', 'convenio')
            ->innerJoin('convenio.carrera', 'carrera')
            ->leftJoin('carrera.nivelAcademico', 'nivelAcademico')
            ->innerJoin('campoClinico.cicloAcademico', 'cicloAcademico')
            ->where('solicitud.id = :solicitud_id')
            ->setParameter('solicitud_id', $id);

        foreach ([
                     'unidad' => 'unidad.nombre',
                     'carrera' => 'carrera.nombre',
                     'cicloAcademico' => 'cicloAcademico.nombre',
                     'nivelAcademico' => 'nivelAcademico.nombre',
                 ] as $filter => $field) {
            if (!empty($filters[$filter])) {
                $qb->andWhere("upper(unaccent({$field})) LIKE UPPER(unaccent(:{$filter}))")
                    ->setParameter($filter, '%' . $filters[$filter] . '%');
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function getAutorizadosBySolicitud(int $id): int
    {
        try {
            $result = $this->getEntityManager()->getConnection()->executeQuery('
                SELECT COUNT(*) AS autorizados
                FROM campo_clinico
                WHERE (lugares_autorizados <> 0 AND lugares_autorizados IS NOT NULL)
                  AND solicitud_id = :id
            ', ['id' => $id])->fetchAssociative();

            return (int)($result['autorizados'] ?? 0);
        } catch (DBALException) {
        }

        return 0;
    }

    public static function getCampoClinicoByReferenciaBancaria(string $referenciaBancaria): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('referenciaBancaria', $referenciaBancaria));
    }

    public function getReporteOportunidadPago(array $filtros): array
    {
        $qb = $this->createQueryBuilder('campo_clinico');
        $qb->innerJoin('campo_clinico.solicitud', 'solicitud')
            ->innerJoin('solicitud.pagos', 'pago',
                $qb->expr()->orX(
                    '(pago.solicitud.referenciaBancaria = solicitud.referenciaBancaria and pago.solicitudId = solicitud.id)',
                    '(pago.solicitud.referenciaBancaria = campo_clinico.referenciaBancaria and pago.solicitudId = campo_clinico.solicitudId)'
                )
            )
            ->innerJoin('campo_clinico.convenio', 'convenio')
            ->where('pago.validado = TRUE')
            ->groupBy('pago.id, campo_clinico.id')
            ->having('pago.id = max(pago.id)')
            ->andWhere('solicitud.isTest = FALSE')
            ->andWhere('pago.isTest = FALSE');

        if (!empty($filtros['desde'])) {
            $qb->andWhere('pago.fechaPago >= :desde')->setParameter('desde', new \DateTime($filtros['desde']));
        }
        if (!empty($filtros['hasta'])) {
            $qb->andWhere('pago.fechaPago <= :hasta')->setParameter('hasta', new \DateTime($filtros['hasta']));
        }
        if (!empty($filtros['search'])) {
            $qb->join('convenio.institucion', 'institucion')
                ->andWhere($qb->expr()->orX(
                    'UNACCENT(LOWER(institucion.nombre)) LIKE UNACCENT(LOWER(:search))',
                    'LOWER(solicitud.referenciaBancaria) LIKE LOWER(:search)'
                ))
                ->setParameter('search', '%' . $filtros['search'] . '%');
        }

        [$paginator, $page, $pageSize, $pagesCount, $totalItems] = $this->setPaginador($qb->getQuery(), $filtros);

        if (!empty($filtros['export'])) {
            $pagos = $paginator->getQuery()->getResult();
        } else {
            $pagos = $paginator->getQuery()
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getResult();
        }

        return [$pagos, $totalItems, $pagesCount, $pageSize];
    }

    private function verificarParametro(array $filtros, string $param): bool
    {
        return array_key_exists($param, $filtros) && $filtros[$param];
    }

    private function procesarFiltrosCampo(QueryBuilder $query, array $filtros): QueryBuilder
    {
        if ($this->verificarParametro($filtros, 'estatus')) {
            $query->andWhere('campo_clinico.estatus = :status')->setParameter('status', $filtros['estatus']);
        }
        if ($this->verificarParametro($filtros, 'fechaIni')) {
            $query->andWhere('campo_clinico.fechaInicial >= :fechaIni')->setParameter('fechaIni', new \DateTime($filtros['fechaIni']));
        }
        if ($this->verificarParametro($filtros, 'fechaFin')) {
            $query->andWhere('campo_clinico.fechaFinal <= :fechaFin')->setParameter('fechaFin', new \DateTime($filtros['fechaFin']));
        }
        if ($this->verificarParametro($filtros, 'unidad')) {
            $query->andWhere('campo_clinico.unidad = :unidad')->setParameter('unidad', $filtros['unidad']);
        }
        if ($this->verificarParametro($filtros, 'delegacion')) {
            $query->andWhere('unidad.delegacion = :delegacion')->setParameter('delegacion', $filtros['delegacion']);
        }

        return $query;
    }

    private function procesarFiltrosConvenio(QueryBuilder $query, array $filtros): QueryBuilder
    {
        if ($this->verificarParametro($filtros, 'carrera')) {
            $query->andWhere('convenio.carrera = :carrera')->setParameter('carrera', $filtros['carrera']);
        }

        return $query;
    }

    private function procesarFiltros(QueryBuilder $query, array $filtros): QueryBuilder
    {
        if ($this->verificarParametro($filtros, 'search')) {
            $query->andWhere('LOWER(solicitud.noSolicitud) LIKE LOWER(:search)')
                ->orWhere('LOWER(institucion.nombre) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $filtros['search'] . '%');
        }

        $query = $this->procesarFiltrosCampo($query, $filtros);

        if ($this->verificarParametro($filtros, 'cicloAcademico')) {
            $query->andWhere('carrera.nivelAcademico = :ciclo')->setParameter('ciclo', $filtros['cicloAcademico']);
        }

        return $this->procesarFiltrosConvenio($query, $filtros);
    }

    private function setPaginador(\Doctrine\ORM\Query $query, array $filtros): array
    {
        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $pageSize = (!empty($filtros['limit']) && $filtros['limit'] > 0) ? $filtros['limit'] : 10;
        $page = (!empty($filtros['page']) && $filtros['page'] > 0) ? $filtros['page'] : 1;
        $pagesCount = (int)ceil($totalItems / $pageSize);

        return [$paginator, $page, $pageSize, $pagesCount, $totalItems];
    }
}
