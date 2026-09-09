<?php

namespace App\Repository\IE\DetalleSolicitudMultiple\Expediente;

use App\ObjectValues\SolicitudId;
use App\Repository\IE\DetalleSolicitud\Expediente\Documents;

interface Expediente
{
    public function expedienteBySolicitud(SolicitudId $solicitudId): Documents;
}
