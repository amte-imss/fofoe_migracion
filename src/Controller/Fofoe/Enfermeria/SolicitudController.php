<?php

namespace App\Controller\Fofoe\Enfermeria;

use App\Controller\DIEControllerController;
use App\Entity\Pago;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/fofoe/enfermeria')]
class SolicitudController extends DIEControllerController
{
    private const DEFAULT_PERPAGE = 10;

    public function __construct(
        protected EntityManagerInterface $em,
        private readonly NormalizerInterface $normalizer,
        protected RequestStack $requestStack,
        protected SerializerInterface $serializer,
    ) {
        parent::__construct($this->requestStack, $this->em, $this->serializer);
    }

    #[Route('/solicitud', name: 'fofoe.enfermeria.index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query->getInt('perPage', self::DEFAULT_PERPAGE);
        $page = $request->query->getInt('page', 1);
        // TODO: total is hardcoded in the legacy code; replace with the real count when pagination is implemented
        $total = 10;
        $status = $request->query->get('estado', '');
        $filters = [
            'name' => $request->query->get('query', ''),
        ];

        $data = $this->em->getRepository(Pago::class)->findEscuelaEnfermeriaByStatus($status, $filters);

        return new JsonResponse([
            'meta' => [
                'perPage' => $perPage,
                'page' => $page,
                'total' => $total,
            ],
            'data' => $this->normalizer->normalize($data, 'json', [
                AbstractNormalizer::ATTRIBUTES => [
                    'id', 'fechaPagoFormatted', 'statusFormatted',
                    'escuelaEnfermeriaSolicitud' => [
                        'nombre', 'statusFormatted',
                        'solicitud' => [
                            'id', 'fechaInicioFormatted', 'fechaFinFormatted',
                            'unidad' => ['nombre', 'id', 'nombreEnfermeria'],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    #[Route('/solicitud/{id}', name: 'fofoe.enfermeria.show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $pago = $this->em->getRepository(Pago::class)->find($id);

        if (!$pago) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $data = $this->normalizer->normalize($pago, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                'id', 'monto', 'fechaPagoFormatted', 'referenciaBancaria', 'tipoMoneda', 'statusFormatted',
                'montoRegistrado', 'observaciones', 'validado', 'requiereFactura',
                'escuelaEnfermeriaSolicitud' => [
                    'id', 'nombre', 'statusFormatted', 'idFormatted', 'email', 'status',
                    'solicitud' => [
                        'id', 'fechaInicioFormatted', 'fechaFinFormatted',
                        'unidad' => ['nombre', 'id', 'createdAtFormatted', 'nombreEnfermeria'],
                    ],
                ],
                'factura' => ['id', 'folio', 'fechaFacturacionFormatted', 'monto'],
            ],
        ]);

        return $this->render('enfermeria/fofoe/show.html.twig', ['pago' => $data]);
    }
}
