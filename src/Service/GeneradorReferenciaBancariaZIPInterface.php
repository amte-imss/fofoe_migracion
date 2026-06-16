<?php

namespace App\Service;

use App\Entity\Solicitud;
use Symfony\Component\HttpFoundation\Response;

interface GeneradorReferenciaBancariaZIPInterface
{
    public function generarZipResponse(Solicitud $solicitud): Response;
}
