<?php

namespace App\Calculator;

use App\Entity\Pago;
use App\Repository\CampoClinicoRepository;
use App\Repository\CampoClinicoRepositoryInterface;
use App\Repository\PagoRepositoryInterface;

class ComprobantePagoCalculator implements ComprobantePagoCalculatorInterface
{
    public function __construct(
        private readonly PagoRepositoryInterface $pagoRepository,
        private readonly CampoClinicoRepositoryInterface $campoClinicoRepository
    ) {
    }

    public function getMontoAPagar(Pago $pago): float
    {
        $comprobantesPago = $this
            ->pagoRepository
            ->getComprobantesPagoByReferenciaBancaria(
                $pago->getReferenciaBancaria()
            );

        $amount = $this->getSubTotal($comprobantesPago);
        if (!$amount) {
            return $this->getPrecio($pago);
        }

        return $this->getPrecio($pago) - floatval($amount);
    }

    private function getPrecio(Pago $pago): float
    {
        if ($pago->getSolicitud()) {
            return $this->getPrecioSolicitudcampo($pago);
        }
        if ($pago->getResidencia()) {
            return $this->getPrecioResidencia($pago);
        }

        return $pago->getMonto();
    }

    private function getPrecioSolicitudcampo(Pago $pago): float
    {
        $solicitud = $pago->getSolicitud();
        if ($solicitud->isPagoUnico()) {
            return $solicitud->getMonto();
        }

        $campoClinico = $solicitud
            ->getCamposClinicos()
            ->matching(
                CampoClinicoRepository::getCampoClinicoByReferenciaBancaria(
                    $pago->getReferenciaBancaria()
                )
            )->first();

        return $campoClinico->getMonto();
    }

    private function getPrecioResidencia(Pago $pago): float
    {
        $residencia = $pago->getResidencia();
        $precio = $residencia->getMonto();
        if ($residencia->getTasaCambio()) {
            $precio = $residencia->getTasaCambio() * $residencia->getMonto();
        }

        return $precio;
    }

    private function getSubTotal($comprobantesPago): float
    {
        return array_reduce(
            $comprobantesPago,
            function ($carry, Pago $pago): float {
                if ($pago->getValidado() === null) {
                    return $carry;
                }
                $carry += floatval($pago->getMonto());
                return $carry;
            },
            0.0
        );
    }
}
