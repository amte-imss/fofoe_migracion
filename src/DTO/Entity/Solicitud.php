<?php

namespace App\DTO\Entity;

use App\Entity\Solicitud as SolicitudBase;

class Solicitud extends SolicitudBase
{
    public function __construct(SolicitudBase $solicitud)
    {
        parent::__construct();

        $this->id                       = $solicitud->id;
        $this->noSolicitud              = $solicitud->noSolicitud;
        $this->fecha                    = $solicitud->fecha;
        $this->estatus                  = $solicitud->estatus;
        $this->referenciaBancaria       = $solicitud->referenciaBancaria;
        $this->monto                    = $solicitud->monto;
        $this->camposClinicos           = $solicitud->camposClinicos;
        $this->montosCarreras           = $solicitud->getMontosCarreras();
        $this->tipoPago                 = $solicitud->tipoPago;
        $this->documento                = $solicitud->documento;
        $this->urlArchivo               = $solicitud->urlArchivo;
        $this->validado                 = $solicitud->validado;
        $this->fechaComprobante         = $solicitud->fechaComprobante;
        $this->observaciones            = $solicitud->observaciones;
        $this->pagos                    = $solicitud->pagos;
        $this->confirmacionOficioAdjunto = $solicitud->confirmacionOficioAdjunto;
    }
}
