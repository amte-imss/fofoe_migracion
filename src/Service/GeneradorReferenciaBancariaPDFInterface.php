<?php

namespace App\Service;

use App\Entity\Solicitud;
use Symfony\Component\Finder\Finder;

interface GeneradorReferenciaBancariaPDFInterface
{
    public function generarPDF(Solicitud $solicitud, string $directoryOutput): Finder;
}
