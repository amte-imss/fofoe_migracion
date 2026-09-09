<?php

namespace App\Event;

use App\Entity\Pago;
use Symfony\Contracts\EventDispatcher\Event;

class PagoEvent extends Event
{
    const PAGO_VALIDADO   = 'pago.comprobante_valido';
    const PAGO_INCORRECTO = 'pago.comprobante_no_valido';

    public function __construct(
        private readonly Pago $pago,
    ) {}

    public function getPago(): Pago
    {
        return $this->pago;
    }
}
