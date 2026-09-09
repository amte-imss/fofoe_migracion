<?php

namespace App\Repository\IE\DetalleSolicitudMultiple\ListaCamposClinicos;

use App\ObjectValues\SolicitudId;

interface CamposClinicos
{
    public function listaCamposClinicosBySolicitud(SolicitudId $solicitudId): array;
}
