<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecuritySubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user     = $event->getAuthenticationToken()->getUser();
        $permisos = $user->getPermisos();

        $record = [
            'request' => [
                '_username' => $this->request?->request->get('_username'),
            ],
        ];

        if ($permisos && count($permisos) > 0) {
            $record['permiso'] = $permisos[0]->getNombre();
        }

        $this->logDB('Inicio de Sesión de Usuario', $record);
    }
}
