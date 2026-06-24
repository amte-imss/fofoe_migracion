<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

final class Carrera
{
    public function __construct(
        private readonly int $id,
        private readonly string $nombre,
        private readonly NivelAcademico $nivelAcademico,
    ) {}

    public function getId(): int { return $this->id; }

    public function getNombre(): string { return $this->nombre; }

    public function getNivelAcademico(): NivelAcademico { return $this->nivelAcademico; }
}
