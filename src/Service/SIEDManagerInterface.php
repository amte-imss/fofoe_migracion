<?php

namespace App\Service;

use App\DTO\Sied;

interface SIEDManagerInterface
{
    public function getDataFromSIEDByMatriculaYClaveDelegacional(string $matricula, int $claveDelegacional): ?Sied;
}
