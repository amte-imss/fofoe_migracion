<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportesFofoeController extends DIEControllerController
{
    #[Route('/fofoe/reportes/', name: 'fofoe.reportes')]
    public function index(): Response
    {
        return $this->render('fofoe/reportes.html.twig');
    }

    #[Route('/fofoe/reportes/pagos', name: 'fofoe.reportes.pagos')]
    public function reportePagos(): Response
    {
        return $this->render('fofoe/reporte_pagos.html.twig');
    }
}
