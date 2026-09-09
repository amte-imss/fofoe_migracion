<?php

namespace App\Event;

use App\Entity\Solicitud;
use Symfony\Contracts\EventDispatcher\Event;

class BankReferencesCreatedEvent extends Event
{
    const NAME = 'bank_references.created';

    public function __construct(
        private readonly Solicitud $solicitud,
    ) {}

    public function getSolicitud(): Solicitud
    {
        return $this->solicitud;
    }
}
