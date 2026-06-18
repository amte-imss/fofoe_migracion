<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

final class CicloAcademico
{
    public function __construct(
        private readonly int $id,
        private readonly string $nombre,
    ) {}

    public function getId(): int { return $this->id; }

    public function getNombre(): string { return $this->nombre; }
}
