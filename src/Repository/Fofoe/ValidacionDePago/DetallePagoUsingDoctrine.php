<?php

namespace App\Repository\Fofoe\ValidacionDePago;

use App\Entity\CampoClinico as CampoClinicoEntity;
use App\Entity\Institucion as InstitucionEntity;
use App\Entity\Pago as PagoEntity;
use App\Entity\Solicitud as SolicitudEntity;
use App\ObjectValues\PagoId;
use App\Repository\CampoClinicoRepository;
use App\Repository\InstitucionRepositoryInterface;
use App\Repository\PagoRepositoryInterface;

final class DetallePagoUsingDoctrine implements DetallePago
{
    public function __construct(
        private readonly PagoRepositoryInterface $pagoRepository,
        private readonly InstitucionRepositoryInterface $institucionRepository
    ) {
    }

    public function detalleByPago(PagoId $pagoId): Pago
    {
        /** @var PagoEntity $pago */
        $pago = $this->pagoRepository->find($pagoId->asInt());
        $solicitud = $pago->getSolicitud();
        $montoTotal = $this->getMontoTotal($solicitud, $pago);
        $unidad = $solicitud->getUnidad();
        $sede = null;
        $carrera = null;
        $horario = null;
        $asignatura = null;
        $campoClinico = null;
        if (!$solicitud->isPagoUnico()) {
            $campoClinico = $this->getCampoClinico($solicitud, $pago);
            $sede = $campoClinico->getUnidad()->getNombre();
            $carrera = $campoClinico->getConvenio()->getCarrera()->getNombre();
            $horario = $campoClinico->getHorario();
            $asignatura = $campoClinico->getAsignatura();
        }

        $pagos = $this->pagoRepository
            ->getComprobantesPagoValidadosByReferenciaBancaria($pago->getReferenciaBancaria(), $pago->getSolicitud()->getId());

        /** @var InstitucionEntity $institucion */
        $institucion = $this->institucionRepository
            ->getInstitucionByPagoId($pago->getId());

        return new Pago(
            $pago->getId(),
            $pago->getReferenciaBancaria(),
            new Solicitud(
                $solicitud->getId(),
                $solicitud->getNoSolicitud(),
                $solicitud->getTipoPago(),
                new CampoClinico(
                    $campoClinico?->getId(),
                    $sede,
                    $carrera,
                    $horario,
                    $asignatura,
                    $campoClinico?->getValidateFormatoFofoe()
                ),
                $unidad && $unidad->getEsUmae(),
                $unidad && $unidad->getEsUmae() ? $unidad->getNombre() : '',
                $solicitud->getValidateOficioMontos()
            ),
            $montoTotal,
            $pago->getMonto(),
            $pago->getComprobantePago(),
            $pago->getFechaPago()->format('Y-m-d'),
            $pago->getMonto(),
            array_map(function (PagoEntity $pago): PagoValidado {
                return new PagoValidado(
                    $pago->getId(),
                    $pago->getReferenciaBancaria(),
                    $pago->getFechaPago(),
                    $pago->getMonto()
                );
            }, $pagos),
            new Institucion(
                $institucion->getId(),
                $institucion->getNombre(),
                $institucion->getRazonSocial(),
                $institucion->getDelegacion()?->getNombre() ?? '',
                $institucion->getRfc()
            ),
            $pago->isRequiereFactura()
        );
    }

    private function getCampoClinico(SolicitudEntity $solicitud, PagoEntity $pago): CampoClinicoEntity
    {
        /** @var CampoClinicoEntity $campoClinico */
        $campoClinico = $solicitud->getCamposClinicos()
            ->matching(
                CampoClinicoRepository::getCampoClinicoByReferenciaBancaria(
                    $pago->getReferenciaBancaria()
                ))
            ->first();
        return $campoClinico;
    }

    private function getMontoTotal(SolicitudEntity $solicitud, PagoEntity $pago): float
    {
        if ($solicitud->isPagoUnico()) {
            $montoTotal = $solicitud->getMonto();
        } else {
            $campoClinico = $this->getCampoClinico($solicitud, $pago);
            $montoTotal = $campoClinico->getMonto();
        }
        return $montoTotal;
    }
}
