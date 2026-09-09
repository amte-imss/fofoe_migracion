<?php

namespace App\DTO\IE\GestionPago;

use App\Entity\Pago;

class PagoDTO implements PagoDTOInterface
{
    public function __construct(
        private readonly Pago $pago,
    ) {}

    public function getReferenciaBancaria(): string
    {
        return $this->pago->getReferenciaBancaria();
    }

    public function getFechaPago(): string
    {
        return $this->pago->getFechaPago()->format('d-m-Y');
    }

    public function getMonto(): float
    {
        return $this->pago->getMonto();
    }

    public function getId(): int
    {
        return $this->pago->getId();
    }

    public function getObservaciones(): ?string
    {
        return $this->pago->getObservaciones();
    }
}
