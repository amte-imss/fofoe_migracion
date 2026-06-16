<?php

namespace App\Repository\IE\DetalleSolicitudMultiple;

use App\ObjectValues\SolicitudId;

interface DetalleSolicitudMultiple
{
    public function getDetalleBySolicitud(SolicitudId $solicitudId): mixed;
}
