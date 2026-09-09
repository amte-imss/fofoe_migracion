<?php

namespace App\Event;

use App\Entity\Factura;
use Symfony\Contracts\EventDispatcher\Event;

class FacturaEvent extends Event
{
    const FACTURA_REGISTRADA = 'factura.uploaded';

    public function __construct(
        private readonly Factura $factura,
    ) {}

    public function getFactura(): Factura
    {
        return $this->factura;
    }
}
