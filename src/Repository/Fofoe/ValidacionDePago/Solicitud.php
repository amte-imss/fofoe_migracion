<?php

namespace App\Repository\Fofoe\ValidacionDePago;

final class Solicitud
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $noSolicitud,
        private readonly ?string $tipoPago,
        private readonly CampoClinico $campoClinico,
        private readonly ?bool $esUMAE,
        private readonly ?string $nombreUnidad,
        private readonly mixed $validateOficioMontos
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoSolicitud(): ?string
    {
        return $this->noSolicitud;
    }

    public function getTipoPago(): ?string
    {
        return $this->tipoPago;
    }

    public function getCampoClinico(): CampoClinico
    {
        return $this->campoClinico;
    }

    public function getNombreUnidad(): ?string
    {
        return $this->nombreUnidad;
    }

    public function getEsUMAE(): ?bool
    {
        return $this->esUMAE;
    }

    public function getValidateOficioMontos(): mixed
    {
        return $this->validateOficioMontos;
    }
}
