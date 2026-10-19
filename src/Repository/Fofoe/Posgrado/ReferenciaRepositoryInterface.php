<?php

namespace App\Repository\Fofoe\Posgrado;

interface ReferenciaRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     *
     * @return array{data: array, total: int}
     */
    public function paginate(int $perPage = 10, int $page = 1, array $filters = []): array;
}
