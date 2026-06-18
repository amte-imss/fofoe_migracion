<?php

namespace App\Repository\Fofoe\ValidacionDePago;

use App\ObjectValues\PagoId;

interface DetallePago
{
    public function detalleByPago(PagoId $pagoId): mixed;
}
