<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

use App\ObjectValues\SolicitudId;

interface CamposClinicos
{
    public function listaCamposClinicosAutorizados(SolicitudId $solicitudId): array;
}
