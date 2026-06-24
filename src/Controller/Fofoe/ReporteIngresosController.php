<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Repository\PagoRepositoryInterface;
use App\Util\CVSUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReporteIngresosController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/fofoe/reporte_ingresos', methods: ['GET'], name: 'fofoe.reporte_ingresos')]
    public function index(Request $request, PagoRepositoryInterface $reporteRepository): Response
    {
        [$filtros, $isSomeValueSet] = $this->setFilters($request);

        if (!$isSomeValueSet) {
            return $this->render('fofoe/reporte_ingresos/index.html.twig');
        }

        $anio     = $filtros['anio'] ?? date('Y');
        $ingresos = $reporteRepository->getReporteIngresosMes($anio);
        $data     = $this->getNormalizeReporteIngresos($ingresos);

        if (!empty($filtros['export'])) {
            $today    = date('Y-m-d');
            $filename = "ReporteIngresosMes{$anio}_{$today}.csv";
            $response = new Response("\xEF\xBB\xBF" . $this->generarCVS($data));
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

            return $response;
        }

        return new JsonResponse(['reporte' => $data]);
    }

    protected function generarCVS(array $datos): string
    {
        $headers = [
            'Mes/Año',
            'Enfermería IMSS Val', 'Enfermería IMSS Pend',
            'Extranjeros Val', 'Extranjeros Pend',
            'Ciclos Clínicos Val', 'Ciclos Clínicos Pend',
            'Internado Médico Val', 'Internado Médico Pend',
            'Edu Per Presencial Val', 'Edu Per Presencial Pend',
            'Edu Per Simulación Val', 'Edu Per Simulación Pend',
            'Edu Per A Distancia Val', 'Edu Per A Distancia Pend',
            'Total Mensual',
        ];

        $cvs = [CVSUtil::arrayToCsvLine($headers)];

        foreach ($datos as $c) {
            $totalMensual = $c['enfVal'] + $c['enfPend']
                + $c['extVal'] + $c['extPend']
                + $c['ccVal']  + $c['ccPend']
                + $c['intVal'] + $c['intPend']
                + $c['presVal'] + $c['presPend']
                + $c['simVal']  + $c['simPend']
                + $c['distVal'] + $c['distPend'];

            $cvs[] = CVSUtil::arrayToCsvLine([
                $c['Mes'] . '/' . $c['Anio'],
                $c['enfVal'],  $c['enfPend'],
                $c['extVal'],  $c['extPend'],
                $c['ccVal'],   $c['ccPend'],
                $c['intVal'],  $c['intPend'],
                $c['presVal'], $c['presPend'],
                $c['simVal'],  $c['simPend'],
                $c['distVal'], $c['distPend'],
                $totalMensual,
            ]);
        }

        return implode("\r\n", $cvs);
    }

    private function setFilters(Request $request): array
    {
        $isSomeValueSet = false;
        $filtros        = [];

        foreach (['export', 'anio'] as $field) {
            $value = $request->query->get($field);
            if (isset($value) && $value !== 'null') {
                $isSomeValueSet  = true;
                $filtros[$field] = $value;
            }
        }

        return [$filtros, $isSomeValueSet];
    }

    private function getNormalizeReporteIngresos(array $datos): array
    {
        return $this->normalizer->normalize($datos, 'json', [
            'attributes' => [
                'Mes', 'Anio',
                'ccVal', 'ccPend', 'intVal', 'intPend',
                'enfVal', 'enfPend', 'extVal', 'extPend',
                'presVal', 'presPend', 'simVal', 'simPend',
                'distVal', 'distPend',
            ],
        ]);
    }
}
