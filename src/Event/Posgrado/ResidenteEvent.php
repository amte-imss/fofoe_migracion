<?php

namespace App\Event\Posgrado;

use App\Entity\Posgrado\Residente;
use Symfony\Contracts\EventDispatcher\Event;

class ResidenteEvent extends Event
{
    const RESIDENTE_DATOS_ACTUALIZADOS = 'residente.datos_actualizados';

    public function __construct(
        private readonly Residente $residente,
    ) {}

    public function getResidente(): Residente
    {
        return $this->residente;
    }
}
