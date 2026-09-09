<?php

namespace App\Event;

use App\Entity\Solicitud;
use Symfony\Contracts\EventDispatcher\Event;

class ReferenciaBancariaDownloadedEvent extends Event
{
    const NAME = 'referencia_bancaria.downloaded';

    public function __construct(
        private readonly Solicitud $solicitud,
        private readonly string    $referencia,
    ) {}

    public function getSolicitud(): Solicitud { return $this->solicitud; }

    public function getReferencia(): string { return $this->referencia; }
}
