<?php

namespace App\Service\Fofoe;

use App\Entity\Pago;
use Symfony\Component\HttpFoundation\Response;

interface GeneradorExpedienteReferenciaZIPInterface
{
    public function generarZipResponse(Pago $pago): Response;
}
