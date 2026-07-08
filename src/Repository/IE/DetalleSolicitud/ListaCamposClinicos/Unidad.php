<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

final class Unidad
{
    public function __construct(
        private readonly string $nombre,
        private readonly bool   $esUmae,
        private readonly string $delegacion,
    ) {}

    public function getNombre(): string { return $this->nombre; }

    public function getEsUmae(): bool { return $this->esUmae; }

    public function getDelegacion(): string { return $this->delegacion; }
}
