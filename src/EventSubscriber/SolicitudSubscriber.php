<?php

namespace App\EventSubscriber;

use App\Entity\Solicitud;
use App\Event\SolicitudEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SolicitudSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SolicitudEvent::SOLICITUD_CREADA    => 'onSolicitudCreada',
            SolicitudEvent::SOLICITUD_VALIDADA  => 'onSolicitudValidada',
            SolicitudEvent::SOLICITUD_TERMINADA => 'onSolicitudTerminada',
            SolicitudEvent::MONTOS_REGISTRADOS  => 'onMontosRegistrados',
            SolicitudEvent::MONTOS_VALIDADOS    => 'onMontosValidados',
            SolicitudEvent::MONTOS_INCORRECTOS  => 'onMontosIncorrectos',
            SolicitudEvent::FORMATOS_GENERADOS  => 'onFormatosGenerados',
            SolicitudEvent::COMPROBANTE_CARGADO => 'onComprobanteCargado',
        ];
    }

    public function onSolicitudCreada(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::SOLICITUD_CREADA, $this->getDataSolicitud($solicitud));
    }

    public function onSolicitudValidada(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::SOLICITUD_VALIDADA, $this->getDataSolicitud($solicitud));
    }

    public function onSolicitudTerminada(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::SOLICITUD_TERMINADA, $this->getDataSolicitud($solicitud));
    }

    public function onMontosRegistrados(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::MONTOS_REGISTRADOS, $this->getDataSolicitud($solicitud));
    }

    public function onMontosValidados(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::MONTOS_VALIDADOS, $this->getDataSolicitud($solicitud));
    }

    public function onMontosIncorrectos(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::MONTOS_INCORRECTOS, $this->getDataSolicitud($solicitud));
    }

    public function onFormatosGenerados(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::FORMATOS_GENERADOS, $this->getDataSolicitud($solicitud));
    }

    public function onComprobanteCargado(SolicitudEvent $event): void
    {
        $solicitud = $event->getSolicitud();
        $this->logDB(SolicitudEvent::COMPROBANTE_CARGADO, $this->getDataSolicitud($solicitud));
    }

    private function getDataSolicitud(Solicitud $solicitud): array
    {
        return [
            'solicitud_id' => $solicitud->getId(),
            'no_solicitud' => $solicitud->getNoSolicitud(),
            'estatus'      => $solicitud->getEstatus(),
        ];
    }
}
