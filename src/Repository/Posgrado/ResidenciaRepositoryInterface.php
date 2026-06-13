<?php

namespace App\Repository\Posgrado;

use Doctrine\Persistence\ObjectRepository;

interface ResidenciaRepositoryInterface extends ObjectRepository
{
    public function getCiclos(string $tipoResidencia): array;

    public function getFoliosYaExistentes(array $folios, string $tipo): array;
}
