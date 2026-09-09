<?php

namespace App\DTO\IE\GestionPago;

use App\Entity\Pago;

final class UltimoPagoDTO implements UltimoPagoDTOInterface
{
    public function __construct(
        private readonly Pago $pago,
    ) {}

    public function getObservaciones(): ?string
    {
        return $this->pago->getObservaciones();
    }
}
