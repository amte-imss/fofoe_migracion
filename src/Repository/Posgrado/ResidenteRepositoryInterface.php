<?php

namespace App\Repository\Posgrado;

use Doctrine\Persistence\ObjectRepository;

interface ResidenteRepositoryInterface extends ObjectRepository
{
    public function getResidentesExtranjeros(array $filters, int $perPage, int $page): array;
}
