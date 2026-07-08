<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

use App\ObjectValues\SolicitudId;

interface CamposClinicos
{
    public function listaCamposClinicosBySolicitud(SolicitudId $solicitudId): array;
}
