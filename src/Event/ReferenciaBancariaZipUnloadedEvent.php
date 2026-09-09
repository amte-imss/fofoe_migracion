<?php

namespace App\Event;

use App\Entity\Solicitud;
use Symfony\Contracts\EventDispatcher\Event;

class ReferenciaBancariaZipUnloadedEvent extends Event
{
    const NAME = 'referencia_bancaria_zip.unloaded';

    public function __construct(
        private readonly Solicitud $solicitud,
    ) {}

    public function getSolicitud(): Solicitud
    {
        return $this->solicitud;
    }
}
