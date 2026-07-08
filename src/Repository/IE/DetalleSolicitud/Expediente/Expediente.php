<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

use App\ObjectValues\SolicitudId;

interface Expediente
{
    public function expedienteBySolicitud(SolicitudId $solicitudId): Documents;
}
