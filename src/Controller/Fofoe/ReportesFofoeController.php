<?php

namespace App\Controller\Fofoe;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fofoe/reportes')]
final class ReportesFofoeController extends AbstractController
{
    #[Route('/', name: 'fofoe.reportes', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_FOFOE_REPORTE_INGS') && !$this->isGranted('ROLE_FOFOE_REPORTE_OP') && !$this->isGranted('ROLE_SUPER')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('fofoe/reportes.html.twig');
    }

    #[Route('/pagos', name: 'fofoe.reportes.pagos', methods: ['GET'])]
    public function pagos(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FOFOE_INICIO');

        return $this->render('fofoe/reporte_pagos.html.twig');
    }
}
