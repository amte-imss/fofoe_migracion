<?php

namespace App\Service\Fofoe;

use App\Entity\Pago;

interface ValidarPagoPosgradoInterface
{
    public function procesar(Pago $pago, ?float $tasaCambio = null): void;
}
