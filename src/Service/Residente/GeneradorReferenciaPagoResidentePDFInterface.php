<?php

namespace App\Service\Residente;

use App\Entity\Posgrado\Residencia;

interface GeneradorReferenciaPagoResidentePDFInterface
{
    /**
     * Returns the raw PDF binary content.
     */
    public function generarPDF(Residencia $residencia): string;
}
