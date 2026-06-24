<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

final class Unidad
{
    public function __construct(
        private readonly string $nombre,
    ) {}

    public function getNombre(): string
    {
        return $this->nombre;
    }
}
