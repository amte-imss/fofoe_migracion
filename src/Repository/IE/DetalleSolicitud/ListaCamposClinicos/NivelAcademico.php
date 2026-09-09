<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

final class NivelAcademico
{
    public function __construct(
        private readonly ?string $nombre,
    ) {}

    public function getNombre(): ?string
    {
        return $this->nombre;
    }
}
