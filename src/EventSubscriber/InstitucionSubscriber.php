<?php

namespace App\EventSubscriber;

use App\Entity\Institucion;
use App\Event\InstitucionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;

class InstitucionSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            InstitucionEvent::DATOS_ACTUALIZADOS => 'onDatosActualizados',
        ];
    }

    public function onDatosActualizados(InstitucionEvent $event): void
    {
        $institucion = $event->getInstitucion();

        $this->logDB('Actualización de datos de institución educativa', [
            'institucion_id' => $institucion->getId(),
        ]);
    }

    public function onCedulaUploaded(Event $event): void
    {
        if (!$event->getObject() instanceof Institucion) {
            return;
        }

        /** @var Institucion $institucion */
        $institucion      = $event->getObject();
        $file             = $institucion->getCedulaFile();
        $originalFilename = 'No Disponible';

        $files = $this->request?->files;

        if ($files?->has('institucion') && isset($files->get('institucion')['cedulaFile'])) {
            $originalFilename = $files->get('institucion')['cedulaFile']->getClientOriginalName();
        } elseif ($files?->has('comprobante_pago') && isset($files->get('comprobante_pago')['cedulaFile'])) {
            $originalFilename = $files->get('comprobante_pago')['cedulaFile']->getClientOriginalName();
        } elseif ($files?->has('institucion') && isset($files->get('institucion')['cedulaFile2'])) {
            $originalFilename = $files->get('institucion')['cedulaFile2']->getClientOriginalName();
        }

        $originalFilename = strlen($originalFilename) > 35
            ? substr($originalFilename, 0, 30) . '...'
            : $originalFilename;

        $baseContext = [
            'institucion_id' => $institucion->getId(),
            'cedula'         => $institucion->getCedulaIdentificacion(),
            'file_name'      => $originalFilename,
        ];

        if (!$file) {
            $this->logDB('Ocurrió un error al intentar cargar la cédula de la IE', $baseContext, 'error');
            return;
        }

        $this->logDB('Archivo cargado: Cédula de Identificación Fiscal', array_merge($baseContext, [
            'type' => $file->getMimeType(),
            'size' => number_format($file->getSize() / 1024.0, 2) . ' Kb',
        ]));
    }
}
