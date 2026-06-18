<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

final class NivelAcademico
{
    public function __construct(
        private readonly string $nombre,
    ) {}

    public function getNombre(): string
    {
        return $this->nombre;
    }
}
