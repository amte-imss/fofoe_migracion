<?php

namespace App\Repository\IE\DetalleSolicitud;

final class UltimoPago
{
    public function __construct(
        private readonly ?int $id = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }
}
