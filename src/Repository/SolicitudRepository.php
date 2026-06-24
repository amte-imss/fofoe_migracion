<?php

namespace App\Repository;

use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SolicitudRepository extends ServiceEntityRepository implements SolicitudRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Solicitud::class);
    }

    public function getAllSolicitudesByInstitucion(
        int $id,
        mixed $tipoPago,
        mixed $estatus,
        mixed $orderBy,
        ?string $search = null,
    ): array {
        $qb = $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.unidad', 'unidad')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('unidad.delegacion', 'delegacion')
            ->andWhere('(solicitud.isTest = false or solicitud.isTest is null)');

        if ($tipoPago !== 'null' && $tipoPago !== '') {
            $qb->andWhere('solicitud.tipoPago = :tipoPago')
                ->setParameter('tipoPago', $tipoPago);
        }

        if ($search !== null && $search !== '') {
            $qb->andWhere("LOWER(solicitud.noSolicitud) LIKE LOWER(:search)")
                ->orWhere("date_format(solicitud.fecha, 'dd/mm/YYYY') LIKE :search")
                ->setParameter('search', '%' . $search . '%');
        }

        if ($estatus !== 'null' && $estatus !== '') {
            $qb->andWhere('solicitud.estatus = :estatus')
                ->setParameter('estatus', $estatus);
        }

        $qb->andWhere('convenio.institucion = :id')
            ->setParameter('id', $id)
            ->orderBy('solicitud.fecha', 'DESC');

        if (
            $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_NO_SOLICITUD_MAYOR_A_MENOR ||
            $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_NO_SOLICITUD_MENOR_A_MAYOR
        ) {
            $order = $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_NO_SOLICITUD_MAYOR_A_MENOR
                ? 'DESC' : 'ASC';
            $qb->orderBy('solicitud.noSolicitud', $order);
        }

        if (
            $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_FECHA_DE_SOLICITUD_MAS_ANTIGUA ||
            $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_FECHA_DE_SOLICITUD_MAS_RECIENTE
        ) {
            $order = $orderBy === SolicitudRepositoryInterface::FILTER_FOR_ORDERING_FECHA_DE_SOLICITUD_MAS_ANTIGUA
                ? 'ASC' : 'DESC';
            $qb->orderBy('solicitud.fecha', $order);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllSolicitudesByDelegacion(
        mixed $delegacion_id = null,
        int $perPage = 10,
        int $offset = 1,
        array $filters = [],
        bool $includeStatusCREADA = false,
    ): array {
        $qb = $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->join('campos_clinicos.unidad', 'unidad')
            ->andWhere('(solicitud.isTest = false or solicitud.isTest is null)')
            ->andWhere('unidad.esUmae = false');

        $this->setFilters($qb, $delegacion_id, $filters);

        if (!$includeStatusCREADA) {
            $qb->andWhere('solicitud.estatus <> :creada')
                ->setParameter('creada', SolicitudInterface::CREADA);
        }

        $qb2 = clone $qb;

        return [
            'data'  => $qb->distinct()
                ->orderBy('solicitud.id', 'DESC')
                ->setMaxResults($perPage)
                ->setFirstResult(($offset - 1) * $perPage)
                ->getQuery()
                ->getResult(),
            'total' => $qb2->select('COUNT(distinct solicitud.id)')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    public function getAllSolicitudesByUnidad(
        mixed $unidad_id = null,
        int $perPage = 10,
        int $offset = 1,
        array $filters = [],
        bool $includeStatusCREADA = false,
    ): array {
        $qb = $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->andWhere('(solicitud.isTest = false or solicitud.isTest is null)');

        $this->setFilters($qb, null, $filters, $unidad_id);

        if (!$includeStatusCREADA) {
            $qb->andWhere('solicitud.estatus <> :creada')
                ->setParameter('creada', SolicitudInterface::CREADA);
        }

        $qb2 = clone $qb;

        return [
            'data'  => $qb->distinct()
                ->orderBy('solicitud.id', 'DESC')
                ->setMaxResults($perPage)
                ->setFirstResult(($offset - 1) * $perPage)
                ->getQuery()
                ->getResult(),
            'total' => $qb2->select('COUNT(distinct solicitud.id)')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    public function getSolicitudesPagadas(int $perPage = 10, int $offset = 1, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('solicitud')
            ->innerJoin('solicitud.pagos', 'pago')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->join('convenio.delegacion', 'delegacion')
            ->leftJoin('pago.factura', 'factura');

        if (!empty($filters['institucion'])) {
            $qb->andWhere('upper(unaccent(institucion.nombre)) like UPPER(unaccent(:institucion))')
                ->setParameter('institucion', '%' . $filters['institucion'] . '%');
        }

        if (!empty($filters['delegacion'])) {
            $qb->andWhere('upper(unaccent(delegacion.nombre)) like UPPER(unaccent(:delegacion))')
                ->setParameter('delegacion', '%' . $filters['delegacion'] . '%');
        }

        if (!empty($filters['referencia'])) {
            $qb->andWhere('upper(unaccent(pago.referenciaBancaria)) like UPPER(unaccent(:referencia))')
                ->setParameter('referencia', '%' . $filters['referencia'] . '%');
        }

        if (!empty($filters['factura'])) {
            $qb->andWhere('upper(unaccent(factura.folio)) like UPPER(unaccent(:factura))')
                ->setParameter('factura', '%' . $filters['factura'] . '%');
        }

        if (!empty($filters['no_solicitud'])) {
            $qb->andWhere('upper(unaccent(solicitud.noSolicitud)) like UPPER(unaccent(:no_solicitud))')
                ->setParameter('no_solicitud', '%' . $filters['no_solicitud'] . '%');
        }

        $qb2 = clone $qb;

        return [
            'data'  => $qb->distinct()
                ->orderBy('solicitud.id', 'DESC')
                ->setMaxResults($perPage)
                ->setFirstResult(($offset - 1) * $perPage)
                ->getQuery()
                ->getResult(),
            'total' => $qb2->select('COUNT(distinct solicitud.id)')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    public function paginationApiSolicitud(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $total = $this->createApiQueryBuilder($filters)
            ->select('count(solicitud.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $this->getApiSolicitud(
            $filters,
            empty($filters) ? $page : null,
            empty($filters) ? $perPage : null,
        )->orderBy('solicitud.id')->getQuery()->getResult();

        return ['data' => $data, 'total' => $total];
    }

    public function getApiSolicitud(array $filters, ?int $page = null, ?int $perPage = null)
    {
        $qb = $this->createApiQueryBuilder($filters);

        if ($page !== null && $perPage !== null) {
            $qb->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);
        }

        return $qb;
    }

    public function getSolicitudesByInstitucion(int $id, array $array_estatus = []): array
    {
        $qb = $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->where('convenio.institucion = :id')
            ->setParameter('id', $id)
            ->orderBy('solicitud.fecha', 'DESC');

        if ($array_estatus) {
            $qb->andWhere('solicitud.estatus IN (:estatus)')
                ->setParameter('estatus', $array_estatus);
        }

        return $qb->getQuery()->getResult();
    }

    public function getSolicitudesByInstitucionAndEstatus(
        mixed $idInstitucion,
        array $array_estatus,
        mixed $ooad = '',
        mixed $umae = null,
    ): ?Solicitud {
        $qb = $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campo')
            ->join('campo.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->join('campo.unidad', 'unidad')
            ->where('institucion = :institucion')
            ->setParameter('institucion', $idInstitucion)
            ->andWhere('solicitud.estatus IN (:estatus)')
            ->setParameter('estatus', $array_estatus)
            ->setMaxResults(1);

        if ($ooad) {
            $qb->andWhere('unidad.delegacion = :del')
                ->setParameter('del', $ooad);
        }

        if ($umae === null) {
            $qb->andWhere('unidad.esUmae = :esUmae')
                ->setParameter('esUmae', false);
        } else {
            $qb->andWhere('unidad = :umae')
                ->setParameter('umae', $umae);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function searchSolicitudes(array $filters)
    {
        $qb = $this->createQueryBuilder('solicitud')
            ->leftJoin('solicitud.pagos', 'pago')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio')
            ->join('convenio.institucion', 'institucion')
            ->join('convenio.delegacionConvenios', 'delegacionConvenios')
            ->join('delegacionConvenios.delegacion', 'delegacion')
            ->leftJoin('pago.factura', 'factura')
            ->orderBy('solicitud.id', 'DESC')
            ->andWhere('(solicitud.isTest = false or solicitud.isTest is null)');

        if (!empty($filters['no_solicitud'])) {
            $qb->where('solicitud.noSolicitud like :no_solicitud')
                ->setParameter('no_solicitud', '%' . strtoupper($filters['no_solicitud']) . '%');
        }

        if (!empty($filters['fecha'])) {
            $qb->andWhere('solicitud.fecha = :fecha')
                ->setParameter('fecha', $filters['fecha']);
        }

        if (!empty($filters['factura'])) {
            $qb->andWhere('upper(unaccent(factura.folio)) like UPPER(unaccent(:factura))')
                ->setParameter('factura', '%' . $filters['factura'] . '%');
        }

        if (!empty($filters['referencia'])) {
            $qb->andWhere('upper(unaccent(pago.referenciaBancaria)) like UPPER(unaccent(:referencia))')
                ->setParameter('referencia', '%' . $filters['referencia'] . '%');
        }

        if (!empty($filters['institucion'])) {
            $qb->andWhere('upper(unaccent(institucion.nombre)) like UPPER(unaccent(:institucion))')
                ->setParameter('institucion', '%' . $filters['institucion'] . '%');
        }

        if (!empty($filters['delegacion'])) {
            $qb->andWhere('upper(unaccent(delegacion.nombre)) like UPPER(unaccent(:delegacion))')
                ->setParameter('delegacion', '%' . $filters['delegacion'] . '%');
        }

        if (!empty($filters['estado'])) {
            $estado = match($filters['estado']) {
                'En validación de CC'      => Solicitud::REGISTRADA,
                'Solicitud revisada'       => Solicitud::CONFIRMADA,
                'En validación de montos'  => Solicitud::EN_VALIDACION_DE_MONTOS_CAME,
                'Montos incorrectos'       => Solicitud::MONTOS_INCORRECTOS_CAME,
                'Montos validados'         => Solicitud::MONTOS_VALIDADOS_CAME,
                'Solicitud Pagada'         => Solicitud::CREDENCIALES_GENERADAS,
                'En espera de facturación' => Solicitud::EN_VALIDACION_FOFOE,
                'Cancelada'                => Solicitud::CANCELADA,
                default                    => $filters['estado'],
            };

            $qb->andWhere('upper(unaccent(solicitud.estatus)) like UPPER(unaccent(:estado))')
                ->setParameter('estado', $estado);
        }

        return $qb->getQuery();
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function createApiQueryBuilder(array $filters)
    {
        return $this->createQueryBuilder('solicitud')
            ->join('solicitud.camposClinicos', 'campos_clinicos')
            ->join('campos_clinicos.convenio', 'convenio');
    }

    private function setFilters(
        $qb,
        mixed $delegacion_id,
        array $filters,
        mixed $unidad_id = null,
    ): void {
        if (!empty($filters['no_solicitud'])) {
            $qb->where('solicitud.noSolicitud like :no_solicitud')
                ->setParameter('no_solicitud', '%' . strtoupper($filters['no_solicitud']) . '%');
        }

        if ($delegacion_id) {
            $qb->andWhere('unidad.delegacion = :delegacion_id')
                ->setParameter('delegacion_id', $delegacion_id);
        }

        if ($unidad_id) {
            $qb->andWhere('campos_clinicos.unidad = :unidad_id')
                ->setParameter('unidad_id', $unidad_id);
        }

        if (!empty($filters['institucion'])) {
            $qb->andWhere('upper(unaccent(institucion.nombre)) like UPPER(unaccent(:institucion))')
                ->setParameter('institucion', '%' . $filters['institucion'] . '%');
        }

        if (!empty($filters['fecha'])) {
            $qb->andWhere('solicitud.fecha = :fecha')
                ->setParameter('fecha', $filters['fecha']);
        }
    }
}
