<?php

namespace App\Calculator;

use App\Entity\Pago;

interface ComprobantePagoCalculatorInterface
{
    public function getMontoAPagar(Pago $pago);
}
