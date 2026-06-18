<?php

namespace App\Repository;

use App\Entity\Unidad;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Unidad>
 */
interface UnidadRepositoryInterface extends ObjectRepository
{
    public function getAllUnidadesByDelegacion(int $delegacion_id = 1, bool $include_umaes = true): array;

    public function getAllUMAEs(): array;

    public function findByClaveDepartamental(string $clave): ?Unidad;

    public function getAllUMAEsNoSelecs(int $id, mixed $usuario = null): array;
}
