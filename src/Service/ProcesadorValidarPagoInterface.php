<?php

namespace App\Service;

use App\Entity\Pago;

interface ProcesadorValidarPagoInterface
{
    public function procesar(Pago $pago): void;
}
