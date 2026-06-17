<?php

namespace App\Controller\Fofoe;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fofoe')]
final class ResumenPagosController extends AbstractController
{
    private const EMPTY_SUMMARY = [
        'pendientes_val' => 0,
        'pendientes_facturacion' => 0,
        'no_validos' => 0,
        'validados_facturados' => 0,
    ];

    #[Route('/resumen', name: 'fofoe.resumen_pagos', methods: ['GET'])]
    public function index(Request $request, Connection $connection): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FOFOE_INICIO');

        $years = $this->getYears($connection);
        $currentYear = (int) $request->query->get('year', (int) date('Y'));

        return $this->render('fofoe/inicio/index.html.twig', [
            'years' => $years,
            'meta' => [
                'year' => $currentYear,
            ],
            'data' => [
                'CC' => $this->fetchViewSummary($connection, 'v_estatus_pago_cc'),
                'POSGRADO' => $this->fetchViewSummary($connection, 'v_estatus_pago_residentes'),
                'POSGRADO_RP' => $this->fetchViewSummary($connection, 'v_estatus_pago_penoimss'),
                'PERMANENTE_PRESENCIAL' => $this->fetchViewSummary($connection, 'v_estatus_pago_eduper_pre'),
                'PERMANENTE_DISTANCIA' => $this->fetchViewSummary($connection, 'v_estatus_pago_eduper_dis'),
                'PERMANENTE_SIMULACION' => $this->fetchViewSummary($connection, 'v_estatus_pago_eduper_sim'),
                'ESCUELA_ENFERMERIA' => $this->fetchViewSummary($connection, 'v_estatus_pago_ef'),
            ],
        ]);
    }

    private function getYears(Connection $connection): array
    {
        try {
            $rows = $connection->fetchAllAssociative(
                "SELECT DISTINCT EXTRACT(YEAR FROM fecha_pago)::int AS year
                 FROM pago
                 WHERE fecha_pago IS NOT NULL
                 ORDER BY year DESC"
            );
        } catch (\Throwable) {
            return [['year' => (int) date('Y')]];
        }

        return $rows ?: [['year' => (int) date('Y')]];
    }

    private function fetchViewSummary(Connection $connection, string $viewName): array
    {
        try {
            $result = $connection->fetchAssociative(sprintf('SELECT * FROM %s', $viewName));
        } catch (\Throwable) {
            $result = null;
        }

        if (!$result) {
            return self::EMPTY_SUMMARY;
        }

        return array_merge(self::EMPTY_SUMMARY, array_intersect_key($result, self::EMPTY_SUMMARY));
    }
}
