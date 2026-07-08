<?php

namespace App\Repository\IE\DetalleSolicitud;

use App\Entity\Pago;
use App\Entity\Solicitud as SolicitudEntity;
use App\ObjectValues\SolicitudId;
use App\Repository\IE\DetalleSolicitud\Expediente\Expediente;
use App\Repository\IE\DetalleSolicitud\ListaCamposClinicos\CamposClinicos;
use App\Repository\IE\DetalleSolicitud\TotalCamposClinicosAutorizados\TotalCamposClinicos;
use App\Repository\SolicitudRepositoryInterface;

final class DetalleSolicitudUsingSql implements DetalleSolicitud
{
    public function __construct(
        private readonly SolicitudRepositoryInterface $solicitudRepository,
        private readonly CamposClinicos               $camposClinicos,
        private readonly TotalCamposClinicos          $totalCamposClinicos,
        private readonly Expediente                   $expediente,
    ) {}

    public function detalleBySolicitud(SolicitudId $solicitudId): Solicitud
    {
        /** @var SolicitudEntity $solicitud */
        $solicitud = $this->solicitudRepository->find($solicitudId->asInt());

        return new Solicitud(
            $solicitud->getId(),
            $solicitud->getEstatusIEFormatted(),
            $solicitud->getNoSolicitud(),
            $this->camposClinicos->listaCamposClinicosBySolicitud($solicitudId),
            $this->totalCamposClinicos->totalCamposClinicosAutorizados($solicitudId),
            $this->expediente->expedienteBySolicitud($solicitudId),
            $this->getLastPago($solicitud)
        );
    }

    private function getLastPago(SolicitudEntity $solicitud): UltimoPago
    {
        if ($solicitud->getPagos()->isEmpty()) {
            return new UltimoPago();
        }

        /** @var Pago $lastPago */
        $lastPago = $solicitud->getPagos()->last();
        return new UltimoPago($lastPago->getId());
    }
}
