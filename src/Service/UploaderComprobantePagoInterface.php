<?php

namespace App\Service;

use App\Entity\Factura;
use App\Entity\Pago;
use App\Entity\Solicitud;

interface UploaderComprobantePagoInterface
{
    public function update(Pago $pago): void;

    public function sendEmailRegistroFactura(Solicitud $solicitud, Pago $pago, Factura $factura): void;
}
