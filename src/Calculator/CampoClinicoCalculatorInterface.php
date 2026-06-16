<?php

namespace App\Calculator;

use App\Entity\CampoClinico;
use App\Entity\Solicitud;

interface CampoClinicoCalculatorInterface
{
    public function getMontoAPagar(CampoClinico $campo, Solicitud $solicitud): float;
}
