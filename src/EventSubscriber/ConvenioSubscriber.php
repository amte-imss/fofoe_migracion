<?php

namespace App\EventSubscriber;

use App\Event\ConvenioEvent;
use App\Normalizer\ConvenioNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConvenioSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConvenioEvent::CONVENIO_CREADO     => 'onConvenioCreado',
            ConvenioEvent::CONVENIO_ACTUALIZADO => 'onConvenioActualizado',
            ConvenioEvent::CONVENIO_ELIMINADO  => 'onConvenioEliminado',
        ];
    }

    public function onConvenioCreado(ConvenioEvent $event): void
    {
        $this->logDB(ConvenioEvent::CONVENIO_CREADO, [
            'convenio' => (new ConvenioNormalizer())->serializer($event->getConvenio()),
        ]);
    }

    public function onConvenioActualizado(ConvenioEvent $event): void
    {
        $this->logDB(ConvenioEvent::CONVENIO_ACTUALIZADO, [
            'convenio' => (new ConvenioNormalizer())->serializer($event->getConvenio()),
            'params'   => $this->request?->request->all() ?? [],
        ]);
    }

    public function onConvenioEliminado(ConvenioEvent $event): void
    {
        $this->logDB(ConvenioEvent::CONVENIO_ELIMINADO, [
            'convenio' => $event->getConvenio()->getId(),
        ]);
    }
}
