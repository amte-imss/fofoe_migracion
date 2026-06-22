<?php

namespace App\Event;

use App\Entity\Solicitud;
use Symfony\Contracts\EventDispatcher\Event;

class SolicitudEvent extends Event
{
    const SOLICITUD_CREADA    = 'solicitud.creada';
    const SOLICITUD_VALIDADA  = 'solicitud.validada';
    const SOLICITUD_TERMINADA = 'solicitud.terminada';
    const MONTOS_REGISTRADOS  = 'solicitud.montos_registrados';
    const MONTOS_VALIDADOS    = 'solicitud.montos_validados';
    const MONTOS_INCORRECTOS  = 'solicitud.montos_incorrectos';
    const FORMATOS_GENERADOS  = 'solicitud.formatos_generados';
    const COMPROBANTE_CARGADO  = 'solicitud.comprobante_cargado';
    const COMPROBANTE_VALIDADO = 'solicitud.comprobante_validado';

    public function __construct(
        private readonly Solicitud $solicitud,
    ) {}

    public function getSolicitud(): Solicitud
    {
        return $this->solicitud;
    }
}
