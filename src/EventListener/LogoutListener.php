<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $base    = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

        if ($session->has('is_convenio')) {
            $session->remove('is_convenio');
            $event->setResponse(new RedirectResponse("{$base}/login/convenio"));
            return;
        }

        if ($session->has('is_residente')) {
            $session->remove('is_residente');
            $event->setResponse(new RedirectResponse("{$base}/login/residente"));
            return;
        }

        if ($session->has('is_enfermeria_alumno')) {
            $solicitudId = $session->get('enfermeria_alumno_solicitud_id');
            $session->remove('is_enfermeria_alumno');
            $session->remove('enfermeria_alumno_solicitud_id');
            $event->setResponse(new RedirectResponse("{$base}/enfermeria-alumno/login/{$solicitudId}"));
            return;
        }

        if ($session->has('is_edu-per_participant')) {
            $solicitudId = $session->get('edu-per_participant_id');
            $session->remove('edu-per_participant_id');
            $session->remove('is_edu-per_participant');
            $event->setResponse(new RedirectResponse("{$base}/edu-per/request/login/{$solicitudId}"));
            return;
        }

        $event->setResponse(new RedirectResponse("{$base}/login"));
    }
}
