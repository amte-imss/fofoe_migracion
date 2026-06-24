<?php

namespace App\Calculator\Posgrado;

use App\Entity\Posgrado\Residencia;

interface ResidenteExtranjeroNoImssCalculatorInterface
{
    public function getMontoAPagar(Residencia $residencia, bool $setVal = false): float;
}
