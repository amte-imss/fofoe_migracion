<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

interface DocumentInterface
{
    public function getNombre(): string;

    public function getFecha(): string;

    public function getDescripcion(): string;

    public function getUrlArchivo(): ?string;
}
