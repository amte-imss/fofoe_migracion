<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

interface GeneradorComprobantePagosZIPInterface
{
    public function generarZipResponse(array $pagos): Response;
}
