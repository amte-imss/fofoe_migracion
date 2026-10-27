<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Repository\Fofoe\ResumenPagosInterface;
use App\Repository\ReferenciaRepositoryInterface;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fofoe')]
class ResumenPagosController extends DIEControllerController
{
    #[Route('/resumen', methods: ['GET'], name: 'fofoe.resumen_pagos')]
    public function index(
        ReferenciaRepositoryInterface $referenciaRepository,
        ResumenPagosInterface $resumenPagos,
        Request $request
    ): Response {
        $years       = $referenciaRepository->getYears();
        $currentYear = $request->request->get('year', Carbon::now()->year);

        return $this->render('fofoe/inicio/index.html.twig', [
            'years' => $years,
            'meta'  => ['year' => $currentYear],
            'data'  => [
                'CC'                    => $resumenPagos->resumenPagosCamposClinicos(['year' => $currentYear])['data'],
                'POSGRADO'              => $resumenPagos->resumenPagosPosgrado(['year' => $currentYear, 'tipo' => 'EXTRANJERO_IMSS'])['data'],
                'POSGRADO_RP'           => $resumenPagos->resumenPagosPosgradoRotacionesParciales(['year' => $currentYear, 'tipo' => 'EXTRANJERO_NO_IMSS'])['data'],
                'PERMANENTE_PRESENCIAL' => $resumenPagos->resumenPagosEduPer(['year' => $currentYear, 'tipo' => 'pre'])['data'],
                'PERMANENTE_DISTANCIA'  => $resumenPagos->resumenPagosEduPer(['year' => $currentYear, 'tipo' => 'dis'])['data'],
                'PERMANENTE_SIMULACION' => $resumenPagos->resumenPagosEduPer(['year' => $currentYear, 'tipo' => 'sim'])['data'],
                'ESCUELA_ENFERMERIA'    => $resumenPagos->resumePagosEscuelaEnfermeria(['year' => $currentYear])['data'],
            ],
        ]);
    }
}
