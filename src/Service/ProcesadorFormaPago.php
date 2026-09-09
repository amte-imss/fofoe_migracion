<?php

namespace App\Service;

use App\Entity\CampoClinico;
use App\Entity\Pago;
use App\Entity\ReferenciaBancariaInterface;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Event\SolicitudEvent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcesadorFormaPago implements ProcesadorFormaPagoInterface
{
    private readonly GeneradorRefenciaBancaria2025 $generadorReferenciaBancaria2025;

    public function __construct(
        private readonly EntityManagerInterface               $entityManager,
        private readonly EventDispatcherInterface             $dispatcher,
    ) {
        $this->generadorReferenciaBancaria2025 = new GeneradorRefenciaBancaria2025($entityManager);
    }

    public function procesar(Solicitud $solicitud): void
    {
        if ($solicitud->getEstatus() !== SolicitudInterface::MONTOS_VALIDADOS_CAME) {
            throw new \Exception('Asignación de tipo de pago no permitida');
        }

        if ($this->isPagoUnico($solicitud)) {
            $pago = $this->createPago($solicitud, $this->getMontoTotal($solicitud->getCamposClinicos()));
            $this->setReferenciaPago($pago, $solicitud);
        } elseif ($this->isPagoMultiple($solicitud)) {
            $index = 0;
            /** @var CampoClinico $camposClinico */
            foreach ($solicitud->getCamposClinicos() as $camposClinico) {
                if ($camposClinico->getLugaresAutorizados() > 0) {
                    $pago = $this->createPago($solicitud, $camposClinico->getMonto());
                    $this->setReferenciaPago($pago, $camposClinico, $index++);
                    $this->entityManager->persist($camposClinico);
                }
            }
        }

        $solicitud->setEstatus(SolicitudInterface::FORMATOS_DE_PAGO_GENERADOS);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SolicitudEvent($solicitud), SolicitudEvent::FORMATOS_GENERADOS);
    }

    private function isPagoUnico(Solicitud $solicitud): bool
    {
        return $solicitud->getTipoPago() === Solicitud::TIPO_PAGO_UNICO;
    }

    protected function isPagoMultiple(Solicitud $solicitud): bool
    {
        return $solicitud->getTipoPago() === Solicitud::TIPO_PAGO_MULTIPLE;
    }

    private function getMontoTotal(Collection $camposClinicos): float
    {
        $total = 0.0;
        /** @var CampoClinico $camposClinico */
        foreach ($camposClinicos as $camposClinico) {
            $total += $camposClinico->getMonto();
        }
        return $total;
    }

    private function createPago(Solicitud $solicitud, float $monto, bool $requireFactura = true): Pago
    {
        $pago = new Pago();
        $pago->setSolicitud($solicitud);
        $pago->setMonto($monto);
        $pago->setRequiereFactura($requireFactura);
        $this->entityManager->persist($pago);
        return $pago;
    }

    private function setReferenciaPago(Pago $pago, ReferenciaBancariaInterface $referenciaBancaria, int $index = 0): void
    {
        $referenciaBancariaResult = $this->generadorReferenciaBancaria2025->generateNextReference($index);
        $pago->setReferenciaBancaria($referenciaBancariaResult);
        $referenciaBancaria->setReferenciaBancaria($referenciaBancariaResult);
    }
}
