<?php

namespace App\Event\Posgrado;

use App\Entity\Posgrado\Residencia;
use Symfony\Contracts\EventDispatcher\Event;

class ReferenciaBancariaResidenteDownloadedEvent extends Event
{
    const NAME = 'referencia_bancaria_residente.downloaded';

    public function __construct(
        private readonly Residencia $residencia,
        private readonly string     $referencia,
    ) {}

    public function getResidencia(): Residencia { return $this->residencia; }

    public function getReferencia(): string { return $this->referencia; }
}
