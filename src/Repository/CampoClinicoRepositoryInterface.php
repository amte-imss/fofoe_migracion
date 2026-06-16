<?php

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;

interface CampoClinicoRepositoryInterface extends ObjectRepository
{
    public function getAllCamposClinicosByInstitucion(int $id): array;

    public function getAllCamposClinicosByRequest(int $id, ?string $search = null, bool $autorizados = false): array;

    public function getTotalSolicitudesByInstitucion(int $id): int;

    public function getDistinctCarrerasBySolicitud(int $id): array;

    public function getAllCamposByPage(array $filtros): array;

    public function getReporteOportunidadPago(array $filtros): array;

    public function getAutorizadosBySolicitud(int $id): int;

    public function getAllCamposClinicos(array $filters): array;

    public function getAllSolicitudes(array $filters, ?int $page = null, ?int $perPage = null): array;

    public function getAllCamposClinicosBySolicitud(int $id, int $perPage, int $page, array $filters = []): array;
}
