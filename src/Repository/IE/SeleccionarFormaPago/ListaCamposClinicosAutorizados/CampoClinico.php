<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

final class CampoClinico
{
    public function __construct(
        private readonly int $id,
        private readonly Unidad $unidad,
        private readonly Convenio $convenio,
        private readonly int $lugaresSolicitados,
        private readonly int $lugaresAutorizados,
        private readonly int $totalTrabajadoresBecados,
        private readonly string $fechaInicial,
        private readonly string $fechaFinal,
        private readonly int $numeroSemanas,
        private readonly float $montoPagar,
        private readonly string $enlaceCalculoCuotas,
        private readonly ?string $horario,
        private readonly ?string $asignatura,
    ) {}

    public function getId(): int { return $this->id; }

    public function getUnidad(): Unidad { return $this->unidad; }

    public function getConvenio(): Convenio { return $this->convenio; }

    public function getLugaresSolicitados(): int { return $this->lugaresSolicitados; }

    public function getLugaresAutorizados(): int { return $this->lugaresAutorizados; }

    public function getTotalTrabajadoresBecados(): int { return $this->totalTrabajadoresBecados; }

    public function getFechaInicial(): string
    {
        return (new \DateTime($this->fechaInicial))->format('d/m/Y');
    }

    public function getFechaFinal(): string
    {
        return (new \DateTime($this->fechaFinal))->format('d/m/Y');
    }

    public function getNumeroSemanas(): int { return $this->numeroSemanas; }

    public function getMontoPagar(): float { return $this->montoPagar; }

    public function getEnlaceCalculoCuotas(): string { return $this->enlaceCalculoCuotas; }

    public function getHorario(): ?string { return $this->horario; }

    public function getAsignatura(): ?string { return $this->asignatura; }
}
