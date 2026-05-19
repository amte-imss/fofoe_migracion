<?php

namespace App\Controller\Came;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/came')]
class SolicitudController extends AbstractController
{
    #[Route('/solicitudes', name: 'came.solicitud.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CAME_MINUS');

        $session     = $request->getSession();
        $delSesion   = $session->get('user_delegacion');
        $unidSesion  = $session->get('user_unidad');
        $isDelActiva = !empty($delSesion) || empty($unidSesion);

        // Resolver OAD activa desde el usuario si la sesión no tiene nada
        $user = $this->getUser();
        $delegacionId = null;
        $unidadId     = null;

        if ($isDelActiva && method_exists($user, 'getDelegaciones')) {
            $delegaciones = $user->getDelegaciones();
            if (!empty($delSesion)) {
                foreach ($delegaciones as $d) {
                    if ((string) $d->getId() === (string) $delSesion) {
                        $delegacionId = $d->getId();
                        break;
                    }
                }
            } elseif (!$delegaciones->isEmpty()) {
                $delegacionId = $delegaciones->first()->getId();
            }
        } elseif (!empty($unidSesion) && method_exists($user, 'getUnidades')) {
            foreach ($user->getUnidades() as $u) {
                if ((string) $u->getId() === (string) $unidSesion) {
                    $unidadId = $u->getId();
                    break;
                }
            }
        }

        return $this->render('came/solicitud/index.html.twig', [
            'delegacionId' => $delegacionId,
            'unidadId'     => $unidadId,
            'isDelActiva'  => $isDelActiva,
        ]);
    }
}
