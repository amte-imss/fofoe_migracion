<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

final class Carrera
{
    public function __construct(
        private readonly string $nombre,
        private readonly NivelAcademico $nivelAcademico,
    ) {}

    public function getNombre(): string { return $this->nombre; }

    public function getNivelAcademico(): NivelAcademico { return $this->nivelAcademico; }
}
