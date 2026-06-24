<?php

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;

interface SolicitudRepositoryInterface extends ObjectRepository
{
    const FILTER_FOR_ORDERING_NO_SOLICITUD_MENOR_A_MAYOR          = 'order_by_no_solicitud_menor_a_mayor';
    const FILTER_FOR_ORDERING_NO_SOLICITUD_MAYOR_A_MENOR          = 'order_by_no_solicitud_mayor_a_menor';
    const FILTER_FOR_ORDERING_FECHA_DE_SOLICITUD_MAS_RECIENTE     = 'order_by_fecha_de_solicitud_mas_reciente';
    const FILTER_FOR_ORDERING_FECHA_DE_SOLICITUD_MAS_ANTIGUA      = 'order_by_fecha_de_solicitud_mas_antigua';

    public function getSolicitudesByInstitucion(int $id, array $array_estatus = []): array;

    public function getAllSolicitudesByInstitucion(
        int $id,
        mixed $tipoPago,
        mixed $estatus,
        mixed $orderBy,
        ?string $search = null,
    ): array;

    public function getSolicitudesPagadas(int $perPage = 10, int $offset = 1, array $filters = []): array;

    public function paginationApiSolicitud(array $filters, ?int $page = null, ?int $perPage = null): array;

    public function getSolicitudesByInstitucionAndEstatus(
        mixed $idInstitucion,
        array $array_estatus,
        mixed $ooad = '',
        mixed $umae = null,
    ): ?object;
}
