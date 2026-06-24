<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Repository\PagoRepositoryInterface;
use App\Repository\ReferenciaRepository;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/fofoe')]
class ReferenciaController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly ReferenciaRepository $referenciaRepository,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pagos/camposClinicos', methods: ['GET', 'POST'], name: 'fofoe/inicio')]
    public function index(Request $request): Response
    {
        $perPage     = $request->query->get('perPage', 10);
        $page        = $request->query->get('page', 1);
        $estado      = $request->query->get('estado', '');
        $referencias = $this->referenciaRepository->paginate($perPage, $page, $request->query->all());
        $years       = $this->referenciaRepository->getYears();

        return $this->render('fofoe/referencia/index.html.twig', [
            'proceso'        => 'Campos Clínicos',
            'categoria_pago' => 'CC',
            'referencias'    => $referencias['data'],
            'years'          => $years,
            'estados'        => [
                'a' => 'Pagos Pendientes Validación',
                'b' => 'Pagos Validados / Facturados',
                'c' => 'Pagos con Factura Pendiente',
                'd' => 'Pagos no Válidos',
            ],
            'meta' => [
                'total'   => $referencias['total'],
                'perPage' => $perPage,
                'page'    => $page,
                'estado'  => $estado,
                'year'    => Carbon::now()->year,
            ],
        ]);
    }

    #[Route('/api/pago', methods: ['GET'], name: 'fofoe.pago.index.api')]
    public function indexApi(Request $request): Response
    {
        $perPage     = $request->query->get('perPage', 10);
        $page        = $request->query->get('page', 1);
        $estado      = $request->query->get('estado', '');
        $referencias = $this->referenciaRepository->paginate($perPage, $page, $request->query->all());

        return $this->jsonResponse([
            'object' => $referencias['data'],
            'meta'   => [
                'total'   => $referencias['total'],
                'perPage' => $perPage,
                'page'    => $page,
                'estado'  => $estado,
            ],
        ]);
    }

    #[Route('/referencia/{id}', methods: ['GET'], name: 'fofoe.referencia.show')]
    public function show(int $id, Request $request, PagoRepositoryInterface $pagoRepository): Response
    {
        $pago = $pagoRepository->find($id);
        if (empty($pago)) {
            throw $this->createNotFoundException('Not found for id ' . $id);
        }

        $pagos = $pagoRepository->findBy([
            'referenciaBancaria' => $pago->getReferenciaBancaria(),
            'solicitudId'        => $pago->getSolicitudId(),
        ]);

        $campos    = $pagos[0]->getCamposPagados()['campos'];
        $solicitud = $pagos[0]->getSolicitud();

        return $this->render('fofoe/detalle_referencia/index.html.twig', [
            'pagos'          => $this->getNormalizePago($pagos),
            'categoria_pago' => 'CC',
            'solicitud'      => $this->getNormalizeSolicitud($solicitud),
            'campos'         => $this->getNormalizeCampo($campos),
        ]);
    }

    private function getNormalizePago(mixed $pago): mixed
    {
        return $this->normalizer->normalize($pago, 'json', [
            'attributes' => [
                'id', 'monto', 'fechaPago', 'fechaPagoFormatted', 'comprobantePago',
                'observaciones', 'requiereFactura', 'facturaGenerada', 'validado', 'referenciaBancaria',
                'factura' => ['id', 'fechaFacturacion', 'folio', 'zip', 'monto'],
            ],
        ]);
    }

    private function getNormalizeCampo(mixed $campo): mixed
    {
        return $this->normalizer->normalize($campo, 'json', [
            'attributes' => [
                'id', 'monto', 'horario', 'asignatura',
                'estatus'         => ['nombre'],
                'displayCarrera',
                'cicloAcademico'  => ['id', 'nombre'],
                'convenio'        => [
                    'cicloAcademico' => ['nombre'],
                    'carrera'        => ['nombre', 'nivelAcademico' => ['nombre']],
                ],
                'lugaresAutorizados', 'totalTrabajadoresBecados',
                'unidad'          => ['nombre', 'esUmae'],
                'displayFechaInicial', 'fechaInicialFormatted',
                'displayFechaFinal', 'formatoFofoeFileName',
            ],
        ]);
    }

    private function getNormalizeSolicitud(mixed $solicitud): mixed
    {
        return $this->normalizer->normalize($solicitud, 'json', [
            'attributes' => [
                'id', 'noSolicitud', 'fecha', 'tipoPago', 'estatus',
                'referenciaBancaria', 'monto', 'fechaComprobanteFormatted',
                'delegacion'  => ['nombre'],
                'institucion' => ['id', 'nombre', 'rfc'],
            ],
        ]);
    }
}
