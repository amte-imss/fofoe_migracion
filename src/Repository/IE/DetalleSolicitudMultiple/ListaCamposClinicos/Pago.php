<?php

namespace App\Repository\IE\DetalleSolicitudMultiple\ListaCamposClinicos;

final class Pago
{
    public function __construct(
        private readonly ?int  $id,
        private readonly ?int  $facturaId,
        private readonly ?bool $requiereFactura,
    ) {}

    public function getId(): ?int { return $this->id; }

    public function getFacturaId(): ?int { return $this->facturaId; }

    public function getRequiereFactura(): ?bool { return $this->requiereFactura; }
}
