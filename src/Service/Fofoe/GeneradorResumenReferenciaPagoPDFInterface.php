<?php

namespace App\Service\Fofoe;

use App\Entity\Pago;

interface GeneradorResumenReferenciaPagoPDFInterface
{
    public function responsePdf(string $path, Pago $pago): string;

    public function getFileName(Pago $pago): string;
}
