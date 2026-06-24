<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Util\CVSUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReporteOportunidadPagoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/fofoe/reporte_oportunidad_pago', methods: ['GET'], name: 'fofoe.reporte_oportunidad')]
    public function index(Request $request): Response
    {
        [$filtros, $isSomeValueSet] = $this->setFilters($request);

        if (!$isSomeValueSet) {
            return $this->render('fofoe/reporte_oportunidad/index.html.twig');
        }

        [$campos, $totalItems, $pagesCount, $pageSize] = $this->em
            ->getRepository(CampoClinico::class)
            ->getReporteOportunidadPago($filtros);

        $datos = $this->getNormalizeCampos($campos);

        if (!empty($filtros['export'])) {
            $today    = date('Y-m-d');
            $filename = "ReporteOportunidadPago_{$today}.csv";
            $response = new Response("\xEF\xBB\xBF" . $this->generarCVS($datos));
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

            return $response;
        }

        return new JsonResponse([
            'reporte'    => $datos,
            'totalItems' => $totalItems,
            'numPags'    => $pagesCount,
            'pageSize'   => $pageSize,
        ]);
    }

    protected function generarCVS(array $datos): string
    {
        $cvs = [CVSUtil::arrayToCsvLine([
            'Consecutivo', 'Número de Solicitud', 'Fecha de Solicitud',
            'Delegación', 'Campo Clínico', 'Carrera',
            'Inicio', 'Fin', 'Institución', 'Alumnos', 'Importe',
            'Referencia', 'Fecha depósito', 'Fecha facturación',
            'Indicador', 'Días',
        ])];

        $indexRow = 0;

        foreach ($datos as $campo) {
            $tiempoPago = $campo['tiempoPago'];

            $cvs[] = CVSUtil::arrayToCsvLine([
                ++$indexRow,
                $campo['solicitud']['noSolicitud'] ?? $campo['solicitud']['id'],
                $campo['solicitud']['fecha'],
                $campo['displayDelegacion'],
                $campo['displayCicloAcademico'],
                $campo['displayCarrera'],
                $campo['fechaInicialFormatted'],
                $campo['fechaFinalFormatted'],
                $campo['solicitud']['institucion']['nombre'],
                $campo['lugaresAutorizados'],
                $campo['monto'],
                $campo['referenciaBancaria'],
                $campo['lastPago']['fechaPagoFormatted'] ?? '',
                $campo['lastPago']['factura']['fechaFacturacionFormatted'] ?? '',
                $tiempoPago > -1000 ? ($tiempoPago >= 14 ? 'CUMPLE' : 'NO CUMPLE') : 'PENDIENTE',
                $tiempoPago > -1000 ? $tiempoPago : '',
            ]);
        }

        return implode("\r\n", $cvs);
    }

    private function setFilters(Request $request): array
    {
        $isSomeValueSet = false;
        $filtros        = [];

        foreach (['desde', 'hasta', 'export', 'page', 'limit', 'search'] as $field) {
            $value = $request->query->get($field);
            if (isset($value) && $value !== 'null') {
                $isSomeValueSet  = true;
                $filtros[$field] = $value;
            }
        }

        return [$filtros, $isSomeValueSet];
    }

    private function getNormalizeCampos(mixed $datos): mixed
    {
        return $this->normalizer->normalize($datos, 'json', [
            'attributes' => [
                'id', 'fechaInicialFormatted', 'fechaFinalFormatted',
                'lugaresSolicitados', 'lugaresAutorizados',
                'displayCicloAcademico', 'displayDelegacion', 'tiempoPago',
                'monto', 'displayCarrera', 'referenciaBancaria',
                'solicitud' => [
                    'id', 'noSolicitud', 'fecha',
                    'institucion' => ['nombre', 'rfc'],
                    'tipoPago',
                ],
                'lastPago' => [
                    'monto', 'fechaPagoFormatted', 'requiereFactura', 'referenciaBancaria',
                    'factura' => ['id', 'folio', 'fechaFacturacionFormatted'],
                ],
            ],
        ]);
    }
}
