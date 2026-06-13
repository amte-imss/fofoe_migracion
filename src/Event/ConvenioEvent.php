<?php

namespace App\Event;

use App\Entity\Convenio;
use Symfony\Contracts\EventDispatcher\Event;

class ConvenioEvent extends Event
{
    const CONVENIO_CREADO     = 'convenio.creado';
    const CONVENIO_ACTUALIZADO = 'convenio.actualizado';
    const CONVENIO_ELIMINADO  = 'convenio.eliminado';

    public function __construct(
        private readonly Convenio $convenio
    ) {}

    public function getConvenio(): Convenio
    {
        return $this->convenio;
    }
}
