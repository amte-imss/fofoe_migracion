<?php

namespace App\EventSubscriber;

use App\Entity\Factura;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class FacturaSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onFacturaCargada',
        ];
    }

    public function onFacturaCargada(Event $event): void
    {
        if (!$event->getObject() instanceof Factura) {
            return;
        }

        /** @var Factura $factura */
        $factura      = $event->getObject();
        $pago         = $factura->getPago();
        $referencia   = $pago?->getReferenciaBancaria() ?? '';
        $solicitud_id = $pago?->getSolicitud()?->getId() ?? '';

        $requestFile = $this->request?->files->get('pago_factura')['factura']['zipFile'] ?? null;

        if (!$requestFile) {
            return;
        }

        $originalFilename = $requestFile->getClientOriginalName();
        $originalFilename = strlen($originalFilename) > 35
            ? substr($originalFilename, 0, 30) . '...'
            : $originalFilename;

        $file = $factura->getZipFile();

        $pagosId = implode(' , ', array_map(
            fn($p) => $p->getId(),
            $factura->getPagos()->toArray()
        ));

        $baseContext = [
            'factura_id'        => $factura->getId(),
            'solicitud_id'      => $solicitud_id,
            'monto_factura'     => $factura->getMonto(),
            'fecha_facturacion' => $factura->getFechaFacturacion()->format('Y-m-d'),
            'zip'               => $factura->getZip(),
            'file_name'         => $originalFilename,
        ];

        if (!$file) {
            $this->logDB(
                'Ocurrió un error al intentar cargar una factura.',
                array_merge($baseContext, $this->getDataPago($pago)),
                'error'
            );
            return;
        }

        $this->logDB('Archivo Cargado. Factura de un pago', array_merge($baseContext, [
            'referencia_bancaria' => $referencia,
            'folio'               => $factura->getFolio(),
            'pagos_id'            => $pagosId,
            'type'                => $file->getMimeType(),
            'size'                => number_format($file->getSize() / 1024.0, 2) . ' Kb',
        ]));
    }
}
