<?php

namespace App\Controller\Fofoe;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fofoe/instituciones/pendientes')]
final class InstitucionPendienteController extends AbstractController
{
    #[Route('', name: 'fofoe.instituciones.pendientes', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FOFOE_DETALLE_INSTITUCION_EDUCATIVA');

        return $this->render('fofoe/instituciones_pendientes/index.html.twig');
    }

    #[Route('/{id}', name: 'fofoe.instituciones.pendientes.show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FOFOE_DETALLE_INSTITUCION_EDUCATIVA');

        return $this->render('fofoe/instituciones_pendientes/show.html.twig', [
            'id' => $id,
        ]);
    }
}
