<?php

namespace App\EventSubscriber\Posgrado;

use App\Event\Posgrado\ReferenciaBancariaResidenteDownloadedEvent;
use App\Event\Posgrado\ResidenteEvent;
use App\EventSubscriber\AbstractSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResidenteSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ResidenteEvent::RESIDENTE_DATOS_ACTUALIZADOS        => 'onDatosActualizados',
            ReferenciaBancariaResidenteDownloadedEvent::NAME     => 'onReferenciaDownloaded',
        ];
    }

    public function onDatosActualizados(ResidenteEvent $event): void
    {
        $residente = $event->getResidente();

        $this->logDB('Datos Actualizados Residente', [
            'residente_id' => $residente->getId(),
            'folio'        => $residente->getFolio(),
            'tipo'         => $residente->getTipo(),
        ]);
    }

    public function onReferenciaDownloaded(ReferenciaBancariaResidenteDownloadedEvent $event): void
    {
        $residencia = $event->getResidencia();

        $this->logDB('Referencia(s) de pago descargada(s)', [
            'residencia_id' => $residencia->getId(),
            'folio'         => $residencia->getFolio(),
            'tipo'          => $residencia->getTipo(),
            'ciclo'         => $residencia->getCiclo(),
        ]);
    }
}
