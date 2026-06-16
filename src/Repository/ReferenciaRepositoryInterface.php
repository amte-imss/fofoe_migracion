<?php

namespace App\Repository;

interface ReferenciaRepositoryInterface
{
    public function paginate(int $perPage = 10, int $offset = 1, array $filters = []): array;

    public function getYears(): array;
}
