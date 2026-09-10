<?php

namespace App\Repository\Fofoe\ValidacionDePago;

final class PagoValidado
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $referenciaBancaria,
        private readonly ?\DateTimeInterface $fechaPago,
        private readonly ?string $monto
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenciaBancaria(): ?string
    {
        return $this->referenciaBancaria;
    }

    public function getFechaPago(): ?string
    {
        return $this->fechaPago?->format('d-m-Y');
    }

    public function getMonto(): ?string
    {
        return $this->monto;
    }
}
