<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

abstract class AbstractDocument implements DocumentInterface
{
    public function __construct(
        private readonly string  $nombre,
        private readonly ?string $fecha,
        private readonly string  $descripcion,
        private readonly ?string $urlArchivo,
        private readonly mixed   $options = null,
    ) {}

    public function getNombre(): string { return $this->nombre; }

    public function getFecha(): string
    {
        if ($this->fecha === null) {
            return 'No Aplica';
        }
        return (new \DateTime($this->fecha))->format('d/m/Y');
    }

    public function getDescripcion(): string { return $this->descripcion; }

    public function getUrlArchivo(): ?string { return $this->urlArchivo; }

    public function getOptions(): mixed { return $this->options; }
}
