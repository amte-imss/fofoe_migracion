<?php

namespace App\Repository\IE\DetalleSolicitud\TotalCamposClinicosAutorizados;

use App\ObjectValues\SolicitudId;

interface TotalCamposClinicos
{
    public function totalCamposClinicosAutorizados(SolicitudId $solicitudId): int;
}
