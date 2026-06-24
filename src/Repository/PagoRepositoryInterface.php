<?php

namespace App\Repository;

use App\Entity\Pago;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Pago>
 */
interface PagoRepositoryInterface extends ObjectRepository
{
    public function getAllPagosByRequest(int $id): array;

    public function getComprobante(string $referenciaBancaria): mixed;

    public function save(Pago $pago): void;

    public function getComprobantesPagoByReferenciaBancaria(string $referenciaBancaria): array;

    public function getReporteIngresosMes(int $anio): array;

    public function getAllPagosByInstitucion(int $id): array;

    public function getComprobantesPagoValidadosByReferenciaBancaria(string $referenciaBancaria, int $solicitudId): array;

    public function findEscuelaEnfermeriaByStatus(string $status, array $filters = []): array;

    public function findEduPerByStatus(string $status, string $type): array;

    public function paginate(int $perPage = 10, int $offset = 1, array $filters = []): array;

    public function paginatePagos(array $filters, ?int $page = null, ?int $perPage = null): array;

    public function getTotalPagosByYear(int $year): int;

    public function getPagoPendienteByEscuelaEnfermeria(int $escuelaEnfermeriaId): ?Pago;

    public function getPagoPendienteByEduPerm(int $escuelaEnfermeriaId): ?Pago;
}
