<?php

namespace App\Repository\IE\DetalleSolicitud;

use App\Repository\IE\DetalleSolicitud\Expediente\Documents;

final class Solicitud
{
    public function __construct(
        private readonly int       $id,
        private readonly string    $estatus,
        private readonly string    $noSolicitud,
        private readonly array     $camposClinicos,
        private readonly int       $totalCamposClinicosAutorizados,
        private readonly Documents $expediente,
        private readonly UltimoPago $ultimoPago,
    ) {}

    public function getId(): int { return $this->id; }

    public function getEstatus(): string { return $this->estatus; }

    public function getNoSolicitud(): string { return $this->noSolicitud; }

    public function getCamposClinicos(): array { return $this->camposClinicos; }

    public function getTotalCamposClinicosAutorizados(): int { return $this->totalCamposClinicosAutorizados; }

    public function getExpediente(): Documents { return $this->expediente; }

    public function getUltimoPago(): UltimoPago { return $this->ultimoPago; }
}
