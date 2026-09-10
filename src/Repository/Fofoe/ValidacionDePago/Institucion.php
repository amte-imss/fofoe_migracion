<?php

namespace App\Repository\Fofoe\ValidacionDePago;

final class Institucion
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $nombre,
        private readonly ?string $razonSocial,
        private readonly ?string $delegacion,
        private readonly ?string $rfc
    ) {
    }

    public function getDelegacion(): ?string
    {
        return $this->delegacion;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRfc(): ?string
    {
        return $this->rfc;
    }
}
