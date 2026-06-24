<?php

namespace App\Repository;

use App\Entity\Institucion;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Institucion>
 */
interface InstitucionRepositoryInterface extends ObjectRepository
{
    public function getInstitucionBySolicitudId(int $id): ?Institucion;

    public function searchOneByNombre(string $nombre): ?Institucion;

    public function getInstitucionByPagoId(int $id): ?Institucion;

    public function searchByNombre(string $nombre): array;

    public function searchByTipoAndNombre(string $tipo, string $nombre): array;

    public function getInstitucionPorRFC(string $rfcInstitucion, bool $esForm = false, ?int $idInstitucion = null): array;

    public function getInstitutionById(int $idInstution): ?Institucion;

    public function paginateAdminInstituciones(array $filters, ?int $page = null, ?int $perPage = null): array;
}
