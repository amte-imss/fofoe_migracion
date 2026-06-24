<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fofoe')]
class InstitucionPendienteController extends DIEControllerController
{
    #[Route('/instituciones/pendientes', name: 'fofoe.instituciones.pendientes', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('fofoe/instituciones_pendientes/index.html.twig');
    }

    #[Route('/instituciones/pendientes/{id}', name: 'fofoe.instituciones.pendientes.show', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        return $this->render('fofoe/instituciones_pendientes/show.html.twig');
    }
}
