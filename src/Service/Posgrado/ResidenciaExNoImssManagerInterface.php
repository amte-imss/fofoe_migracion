<?php

namespace App\Service\Posgrado;

use App\Entity\Posgrado\Residencia;

interface ResidenciaExNoImssManagerInterface
{
    public function registrarResidenciaExNoImss(Residencia $residencia): void;

    public function actualizarResidenciaExNoImss(Residencia $residencia): void;

    public function sendEmailBienvenida(Residencia $residencia): void;
}
