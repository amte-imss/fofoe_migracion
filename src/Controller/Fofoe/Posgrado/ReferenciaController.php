<?php

namespace App\Controller\Fofoe\Posgrado;

use App\Controller\DIEControllerController;
use App\Entity\Pago;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Repository\Fofoe\Posgrado\ReferenciaRepositoryInterface as ReferenciaPosgradoRepositoryInterface;
use App\Repository\PagoRepositoryInterface;
use App\Repository\ReferenciaRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/fofoe')]
class ReferenciaController extends DIEControllerController
{
    private const string TIPO_REQUIREMENT = 'imss|noimss';

    private const array ESTADOS = [
        '' => '',
        'pendiente_val' => 'Pagos Pendientes Validación',
        'validado_facturado' => 'Pagos Validados / Facturados',
        'pendiente_factura' => 'Pagos con Factura Pendiente',
        'no_valido' => 'Pagos no Válidos',
    ];

    #[Route('/pagos/posgrado/{tipo}', name: 'fofoe.posgrado.inicio', methods: ['GET', 'POST'], requirements: ['tipo' => self::TIPO_REQUIREMENT])]
    public function index(
        Request $request,
        ReferenciaPosgradoRepositoryInterface $referenciaRepositoryPosgrado,
        ReferenciaRepositoryInterface $referenciaRepository,
        string $tipo = 'imss',
    ): Response {
        [$referencias, $meta] = $this->paginate($request, $referenciaRepositoryPosgrado, $tipo);

        return $this->render('fofoe/referencia/index.html.twig', [
            'proceso' => $tipo === 'imss'
                ? 'Alumnos extranjeros en Ciclo académico anual'
                : 'Alumnos extranjeros rotación parcial de especialidad',
            'categoria_pago' => 'POSGRADO',
            'referencias' => $referencias,
            'years' => $referenciaRepository->getYears(),
            'estados' => self::ESTADOS,
            'meta' => [...$meta, 'year' => (int) date('Y')],
        ]);
    }

    #[Route('/api/pago/posgrado/{tipo}', name: 'fofoe.pago.posgrado.index.api', methods: ['GET'], requirements: ['tipo' => self::TIPO_REQUIREMENT])]
    public function indexApi(
        Request $request,
        ReferenciaPosgradoRepositoryInterface $referenciaRepository,
        string $tipo = 'imss',
    ): JsonResponse {
        [$referencias, $meta] = $this->paginate($request, $referenciaRepository, $tipo);

        return $this->jsonResponse([
            'object' => $referencias,
            'meta' => $meta,
        ]);
    }

    #[Route('/referencia/posgrado/{id:pago}', name: 'fofoe.posgrado.referencia.show', methods: ['GET'])]
    public function show(
        Pago $pago,
        PagoRepositoryInterface $pagoRepository,
        NormalizerInterface $normalizer,
    ): Response {
        $residencia = $pago->getResidencia()
            ?? throw $this->createNotFoundException('Not found for id ' . $pago->getId());

        $pagos = $pagoRepository->findBy([
            'referenciaBancaria' => $pago->getReferenciaBancaria(),
            'residencia' => $residencia,
        ]);

        return $this->render('fofoe/detalle_referencia/index.html.twig', [
            'pagos' => $this->normalizePagos($normalizer, $pagos),
            'categoria_pago' => 'POSGRADO',
            'tipo' => $residencia->getTipo() === ResidenciaInterface::TIPO_EXTRANJERO_IMSS ? 'imss' : 'noimss',
            'residencia' => $this->normalizeResidencia($normalizer, $residencia),
        ]);
    }

    /**
     * @return array{0: array, 1: array{total: int, perPage: int, page: int, estado: string}}
     */
    private function paginate(Request $request, ReferenciaPosgradoRepositoryInterface $repository, string $tipo): array
    {
        $perPage = $request->query->getInt('perPage', 10);
        $page = $request->query->getInt('page', 1);
        $estado = $request->query->getString('estado');

        $filters = [...$request->query->all(), 'tipo' => $tipo];
        $result = $repository->paginate($perPage, $page, $filters);

        return [
            $result['data'],
            [
                'total' => $result['total'],
                'perPage' => $perPage,
                'page' => $page,
                'estado' => $estado,
            ],
        ];
    }

    /**
     * @param Pago[] $pagos
     */
    private function normalizePagos(NormalizerInterface $normalizer, array $pagos): array
    {
        return $normalizer->normalize($pagos, 'json', [
            'attributes' => [
                'id',
                'monto',
                'fechaPago',
                'fechaPagoFormatted',
                'comprobantePago',
                'observaciones',
                'requiereFactura',
                'facturaGenerada',
                'residencia' => ['id'],
                'validado',
                'referenciaBancaria',
                'factura' => [
                    'id',
                    'fechaFacturacion',
                    'folio',
                    'zip',
                    'monto',
                ],
            ],
        ]);
    }

    private function normalizeResidencia(NormalizerInterface $normalizer, Residencia $residencia): array
    {
        return $normalizer->normalize($residencia, 'json', [
            'attributes' => [
                'id',
                'especialidad',
                'sede', 'subsede',
                'folio',
                'tipoDelegacionUmae',
                'monto',
                'tipoMoneda',
                'tasaCambio',
                'tipo',
                'grado',
                'residente' => [
                    'id',
                    'usuario' => [
                        'id', 'nombre', 'apellidoPaterno', 'apellidoMaterno',
                        'rfc', 'correo', 'telefono',
                    ],
                ],
                'ciclo',
                'lastPago' => ['id'],
            ],
        ]);
    }
}
