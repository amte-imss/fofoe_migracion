<?php

namespace App\Event\Posgrado;

use App\Entity\Posgrado\Residencia;
use Symfony\Contracts\EventDispatcher\Event;

class ResidenciaEvent extends Event
{
    const RESIDENCIA_REGISTRADA = 'residencia.registrada';
    const RESIDENCIA_ACTUALIZADA = 'residencia.actualizada';

    public function __construct(
        private readonly Residencia $residencia
    ) {}

    public function getResidencia(): Residencia
    {
        return $this->residencia;
    }
}
