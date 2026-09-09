<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

class CampoClinico
{
    public function __construct(
        private readonly int     $id,
        private readonly Convenio $convenio,
        private readonly int     $lugaresSolicitados,
        private readonly ?int     $lugaresAutorizados,
        private readonly int     $totalTrabajadoresBecados,
        private readonly string  $fechaInicial,
        private readonly string  $fechaFinal,
        private readonly Unidad  $unidad,
        private readonly int     $noSemanas,
        private readonly ?string $obsValRegistro,
        private readonly ?string $horario,
        private readonly ?string $asignatura,
    ) {}

    public function getId(): int { return $this->id; }

    public function getConvenio(): Convenio { return $this->convenio; }

    public function getLugaresSolicitados(): int { return $this->lugaresSolicitados; }

    public function getLugaresAutorizados(): ?int { return $this->lugaresAutorizados; }

    public function getTotalTrabajadoresBecados(): int { return $this->totalTrabajadoresBecados; }

    public function getFechaInicial(): string
    {
        return (new \DateTime($this->fechaInicial))->format('d/m/Y');
    }

    public function getFechaFinal(): string
    {
        return (new \DateTime($this->fechaFinal))->format('d/m/Y');
    }

    public function getUnidad(): Unidad { return $this->unidad; }

    public function getNoSemanas(): int { return $this->noSemanas; }

    public function getObsValRegistro(): ?string { return $this->obsValRegistro; }

    public function getHorario(): ?string { return $this->horario; }

    public function getAsignatura(): ?string { return $this->asignatura; }
}
