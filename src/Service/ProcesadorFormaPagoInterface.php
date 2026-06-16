<?php

namespace App\Service;

use App\Entity\Solicitud;

interface ProcesadorFormaPagoInterface
{
    public function procesar(Solicitud $solicitud): void;
}
