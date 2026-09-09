<?php

namespace App\EventSubscriber;

use App\Entity\Pago;
use App\Event\PagoEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class PagoSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD    => 'onComprobanteCargado',
            PagoEvent::PAGO_VALIDADO   => 'onPagoValidado',
            PagoEvent::PAGO_INCORRECTO => 'onPagoIncorrecto',
        ];
    }

    public function onComprobanteCargado(Event $event): void
    {
        if (!$event->getObject() instanceof Pago) {
            return;
        }

        /** @var Pago $pago */
        $pago        = $event->getObject();
        $requestFile = $this->request?->files->get('comprobante_pago')['comprobantePagoFile'] ?? null;

        $originalFilename = $requestFile
            ? $requestFile->getClientOriginalName()
            : 'ADMIN UPLOAD';

        $originalFilename = strlen($originalFilename) > 35
            ? substr($originalFilename, 0, 30) . '...'
            : $originalFilename;

        $file = $pago->getComprobantePagoFile();

        if (!$file) {
            $this->logDB(
                'Ocurrió un error al intentar cargar el comprobante de pago.',
                array_merge($this->getDataPago($pago), [
                    'comprobante'      => $pago->getComprobantePago(),
                    'file_name'        => $originalFilename,
                    'requiere_factura' => $pago->isRequiereFactura(),
                ]),
                'error'
            );
            return;
        }

        if ($pago->getEscuelaEnfermeriaSolicitud()) {
            $this->logDB('Archivo cargado. Comprobante de pago.', array_merge($this->getDataPago($pago), [
                'monto'       => $pago->getMonto(),
                'fecha_pago'  => $pago->getFechaPago()->format('Y-m-d'),
                'comprobante' => $pago->getComprobantePago(),
                'file_name'   => $originalFilename,
                'type'        => $file->getMimeType(),
                'size'        => number_format($file->getSize() / 1024.0, 2) . ' Kb',
            ]));
        }
    }

    public function onPagoIncorrecto(PagoEvent $event): void
    {
        $pago = $event->getPago();

        $this->logDB('Se ha registrado que el comprobante de pago NO es válido.',
            array_merge($this->getDataPago($pago), [
                'monto'         => $pago->getMonto(),
                'fecha_pago'    => $pago->getFechaPago()->format('Y-m-d'),
                'observaciones' => $pago->getObservaciones(),
            ])
        );
    }

    public function onPagoValidado(PagoEvent $event): void
    {
        $pago = $event->getPago();

        $this->logDB('Se ha confirmado que el comprobante de pago es válido.',
            array_merge($this->getDataPago($pago), [
                'monto'      => $pago->getMonto(),
                'fecha_pago' => $pago->getFechaPago()->format('Y-m-d'),
            ])
        );
    }

    private function getDataPago(Pago $pago): array
    {
        $solicitud  = $pago->getSolicitud();
        $residencia = $pago->getResidencia();

        return [
            'pago_id'      => $pago->getId(),
            'solicitud_id' => $solicitud?->getId(),
            'residencia_id'=> $residencia?->getId(),
            'tipo_pago'    => $solicitud?->getTipoPago(),
            'referencia'   => $pago->getReferenciaBancaria(),
        ];
    }
}
