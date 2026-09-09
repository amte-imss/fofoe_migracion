<?php

namespace App\DTO\IE\GestionPago;

use App\Entity\CampoClinico;

final class CampoClinicoDTO
{
    public function __construct(
        private readonly CampoClinico $campoClinico,
    ) {}

    public function getSede(): string
    {
        $unidad = $this->campoClinico->getUnidad();
        return $unidad !== null ? $unidad->getNombre() : 'Sin sede';
    }

    public function getCarrera(): string
    {
        $carrera = $this->campoClinico->getConvenio()->getCarrera();
        return $carrera !== null ? $carrera->getDisplayName() : 'Sin carrera';
    }

    public function getTipoCampoClinico(): int
    {
        return $this->campoClinico->getCicloAcademico()->getId();
    }

    public function getCicloAcademico(): string
    {
        return $this->campoClinico->getDisplayCicloAcademico();
    }

    public function getHorario(): ?string
    {
        return $this->campoClinico->getHorario();
    }

    public function getAsignatura(): ?string
    {
        return $this->campoClinico->getAsignatura();
    }
}
