<?php

namespace App\Repository\Fofoe\ValidacionDePago;

final class Pago
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $referenciaBancaria,
        private readonly Solicitud $solicitud,
        private readonly ?string $montoTotal,
        private readonly ?string $montoPendienteValidar,
        private readonly ?string $comprobantePago,
        private readonly ?string $fechaPago,
        private readonly ?string $monto,
        private readonly array $historial,
        private readonly Institucion $institucion,
        private readonly ?bool $requiereFactura
    ) {
    }

    public function getSolicitud(): Solicitud
    {
        return $this->solicitud;
    }

    public function getMontoTotal(): ?string
    {
        return $this->montoTotal;
    }

    public function getMontoPendienteValidar(): ?string
    {
        return $this->montoPendienteValidar;
    }

    public function getComprobantePago(): ?string
    {
        return $this->comprobantePago;
    }

    public function getFechaPago(): ?string
    {
        return $this->fechaPago;
    }

    public function getMonto(): ?string
    {
        return $this->monto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHistorial(): array
    {
        return $this->historial;
    }

    public function getInstitucion(): Institucion
    {
        return $this->institucion;
    }

    public function getRequiereFactura(): ?bool
    {
        return $this->requiereFactura;
    }

    public function getReferenciaBancaria(): ?string
    {
        return $this->referenciaBancaria;
    }
}
