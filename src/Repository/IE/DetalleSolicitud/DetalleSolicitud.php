<?php

namespace App\Repository\IE\DetalleSolicitud;

use App\ObjectValues\SolicitudId;

interface DetalleSolicitud
{
    public function detalleBySolicitud(SolicitudId $solicitudId): mixed;
}
