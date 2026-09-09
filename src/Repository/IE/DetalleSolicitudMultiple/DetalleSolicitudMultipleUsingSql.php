<?php

namespace App\Repository\IE\DetalleSolicitudMultiple;

use App\Entity\Solicitud as SolicitudEntity;
use App\ObjectValues\SolicitudId;
use App\Repository\IE\DetalleSolicitud\Solicitud;
use App\Repository\IE\DetalleSolicitud\TotalCamposClinicosAutorizados\TotalCamposClinicos;
use App\Repository\IE\DetalleSolicitud\UltimoPago;
use App\Repository\IE\DetalleSolicitudMultiple\Expediente\Expediente;
use App\Repository\IE\DetalleSolicitudMultiple\ListaCamposClinicos\CamposClinicos;
use App\Repository\SolicitudRepositoryInterface;

final class DetalleSolicitudMultipleUsingSql implements DetalleSolicitudMultiple
{
    public function __construct(
        private readonly SolicitudRepositoryInterface $solicitudRepository,
        private readonly TotalCamposClinicos          $totalCamposClinicos,
        private readonly Expediente                   $expediente,
        private readonly CamposClinicos               $camposClinicos,
    ) {}

    public function getDetalleBySolicitud(SolicitudId $solicitudId): Solicitud
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
            new UltimoPago()
        );
    }
}
