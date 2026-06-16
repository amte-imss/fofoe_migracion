<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\DTO\IE\InicioDTO;
use App\Entity\CampoClinico;
use App\Entity\Institucion;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Entity\SolicitudTipoPagoInterface;
use App\Form\Type\ComprobantePagoType\SolicitudComprobantePagoType;
use App\Form\Type\FormaPagoType;
use App\Form\Type\RegistoMontos\SolicitudRegistroMontosType;
use App\Form\Type\RegistraCampoClinico\CampoClinicoType;
use App\ObjectValues\SolicitudId;
use App\Repository\CampoClinicoRepositoryInterface;
use App\Repository\IE\DetalleSolicitud\DetalleSolicitud;
use App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados\CamposClinicos;
use App\Repository\SolicitudRepositoryInterface;
use App\Service\CampoClinicoManagerInterface;
use App\Service\GeneradorReferenciaBancariaZIPInterface;
use App\Service\ProcesadorFormaPagoInterface;
use App\Service\SolicitudManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
class SolicitudController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly SolicitudRepositoryInterface $solicitudRepository,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/inicio', name: 'ie#inicio', methods: ['GET'])]
    #[IsGranted('ROLE_IE')]
    public function inicio(Request $request, PaginatorInterface $paginator): Response
    {
        $institucion = $this->checkPermissionInstitucion();

        [$isOffsetSet, $isSearchSet, $isTipoPagoSet, $isPerPageSet, $isOrderBySet, $isEstatusSet]
            = $this->setFilters($request);
        [$offset, $search, $tipoPago, $perPage, $orderBy, $estatus]
            = $this->initializeFiltersWithDefaultValues($request);

        $solicitudes = $this->solicitudRepository->getAllSolicitudesByInstitucion(
            $institucion->getId(), $tipoPago, $estatus, $orderBy, $search
        );

        $usuario = $this->getUser();
        $solicitudes = array_filter($solicitudes, function ($solicitud) use ($institucion, $usuario) {
            if ($usuario && $institucion->isUsersByCampus()) {
                return ($solicitud->getCampus() == null)
                    || ($usuario->getCampus() != null
                        && $solicitud->getCampus()->getId() == $usuario->getCampus()->getId());
            }
            return true;
        });

        $collection = new ArrayCollection();
        foreach ($solicitudes as $solicitud) {
            $collection->add(new InicioDTO($solicitud));
        }

        if ($this->isRequestedToFilter($isOffsetSet, $isSearchSet, $isTipoPagoSet, $isPerPageSet, $isOrderBySet, $isEstatusSet)) {
            $paginatorData = $paginator->paginate($collection, $offset, $perPage);

            return new JsonResponse([
                'camposClinicos' => $this->normalizer->normalize(
                    array_values(array_filter($paginatorData->getItems()->getArrayCopy())),
                    'json',
                    [
                        'attributes' => [
                            'id', 'estatus', 'estatusIEFormatted', 'fecha',
                            'noCamposAutorizados', 'noCamposSolicitados',
                            'displayDelegacionUmae', 'noSolicitud', 'tipoPago', 'ultimoPago',
                        ],
                    ]
                ),
                'paginationData' => $paginatorData->getPaginationData(),
            ]);
        }

        return $this->render('ie/solicitud/inicio.html.twig');
    }

    #[Route('/solicitudes/nueva', name: 'ie#nueva_solicitud', methods: ['GET'])]
    public function registroSolicitud(): Response
    {
        $this->checkPermissionInstitucion();

        return $this->render('ie/solicitud/registro_de_solicitud.html.twig', [
            'solicitud' => null,
        ]);
    }

    #[Route('/solicitudes/{id}/editar', name: 'ie#editar_solicitud', methods: ['GET'])]
    public function editarSolicitud(int $id): Response
    {
        $this->checkPermissionInstitucion();
        $solicitud = $this->checkPermissionSolicitud($id);

        if ($solicitud->getEstatus() !== SolicitudInterface::CREADA) {
            throw $this->createAccessDeniedException('No es posible editar la solicitud');
        }

        return $this->render('ie/solicitud/registro_de_solicitud.html.twig', [
            'solicitud' => $this->normalizer->normalize($solicitud, 'json', [
                'attributes' => [
                    'id', 'esUMAE', 'displayDelUMAE', 'idDelUMAE',
                    'campoClinicos' => [
                        'id', 'asignatura', 'promocion',
                        'convenio' => [
                            'cicloAcademico' => ['id', 'nombre'],
                            'id', 'vigencia', 'vigenciaFormatted', 'label',
                            'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                        ],
                        'cicloAcademico'     => ['id', 'nombre'],
                        'lugaresSolicitados', 'lugaresAutorizados', 'horario',
                        'trabajadoresBecados' => ['matricula'],
                        'unidad'             => ['id', 'nombre'],
                        'fechaInicial', 'fechaFinal', 'fechaInicialFormatted', 'fechaFinalFormatted',
                    ],
                    'institucion' => [
                        'id', 'nombre', 'fax', 'telefono', 'extension',
                        'correo', 'sitioWeb', 'direccion', 'rfc', 'representante',
                        'convenios' => [
                            'id', 'nombre',
                            'carrera'        => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                            'cicloAcademico' => ['id', 'nombre'],
                            'vigencia', 'vigenciaFormatted', 'label',
                        ],
                    ],
                ],
            ]),
        ]);
    }

    #[Route('/solicitudes/{id}/detalle-de-solicitud', name: 'ie#detalle_de_solicitud', methods: ['GET'])]
    public function detalleDeSolicitud(int $id, DetalleSolicitud $detalleSolicitud): Response
    {
        $this->checkPermissionInstitucion();

        $solicitudObject = $this->solicitudRepository->find($id);
        if (!$solicitudObject) {
            $this->createNotFindSolicitudException($id);
        }

        $solicitud = $detalleSolicitud->detalleBySolicitud(SolicitudId::fromString($solicitudObject->getId()));

        return $this->render('ie/solicitud/detalle_de_solicitud.html.twig', [
            'solicitud'       => $this->normalizer->normalize($solicitud, 'json'),
            'solicitudObject' => $solicitudObject,
        ]);
    }

    #[Route('/solicitudes/{id}/registrar-montos', name: 'ie#registrar_montos', methods: ['POST', 'GET'])]
    #[Route('/solicitudes/{id}/corregir-montos', name: 'ie#corregir_montos', methods: ['POST', 'GET'])]
    public function registrarMontos(
        int $id,
        Request $request,
        CampoClinicoRepositoryInterface $campoClinicoRepository,
        SolicitudManagerInterface $solicitudManager
    ): Response {
        $institucion = $this->checkPermissionInstitucion();
        $solicitud   = $this->checkPermissionSolicitud($id);

        if (
            $solicitud->getEstatus() != SolicitudInterface::CONFIRMADA &&
            $solicitud->getEstatus() != SolicitudInterface::MONTOS_INCORRECTOS_CAME
        ) {
            $this->addFlash('danger', 'No puede realizar esta acción en este momento');
            return $this->redirectToRoute('ie#inicio');
        }

        $autorizados = $campoClinicoRepository->getAutorizadosBySolicitud($id);
        $carreras    = $campoClinicoRepository->getDistinctCarrerasBySolicitud($id);

        $originalDescuentos = [];
        foreach ($solicitud->getMontosCarreras() as $monto) {
            $originalDescuentos[$monto->getId()] = [];
            foreach ($monto->getDescuentos() as $descuento) {
                if ($descuento->getId()) {
                    $originalDescuentos[$monto->getId()][$descuento->getId()] = $descuento;
                }
            }
        }

        $form = $this->createForm(SolicitudRegistroMontosType::class, $solicitud, [
            'action' => $this->generateUrl('ie#registrar_montos', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitudManager->registrarMontos($form->getData(), $originalDescuentos);
            $this->addFlash('success', 'Se han guardado correctamente los montos para la solicitud ' . $solicitud->getNoSolicitud());
            return $this->redirectToRoute('ie#inicio');
        }

        return $this->render('ie/solicitud/registrar_montos.html.twig', [
            'institucion' => $institucion,
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
            'carreras'    => $carreras,
            'route'       => $request->attributes->get('_route'),
            'autorizados' => $autorizados,
            'montos'      => $this->normalizer->normalize($solicitud, 'json', [
                'attributes' => [
                    'montosCarreras' => [
                        'montoInscripcion', 'montoColegiatura',
                        'descuentos' => ['numAlumnos', 'descuentoInscripcion', 'descuentoColegiatura'],
                        'carrera'    => ['id', 'nombre', 'nivelAcademico' => ['nombre']],
                    ],
                ],
            ]),
            'errors'      => $this->getFormErrors($form),
        ]);
    }

    #[Route('/solicitudes/{id}/seleccionar-forma-de-pago', name: 'ie#seleccionar_forma_de_pago')]
    public function seleccionarFormaDePago(
        int $id,
        Request $request,
        ProcesadorFormaPagoInterface $procesadorFormaPago,
        CamposClinicos $camposClinicos
    ): Response {
        $institucion = $this->checkPermissionInstitucion();
        $solicitud   = $this->checkPermissionSolicitud($id);

        $form = $this->createForm(FormaPagoType::class, $solicitud, [
            'action' => $this->generateUrl('ie#seleccionar_forma_de_pago', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $procesadorFormaPago->procesar($form->getData());
            $this->addFlash('success', sprintf(
                'Se ha guardado correctamente la opción por Pago %s para la solicitud %s',
                $solicitud->getTipoPago(),
                $solicitud->getNoSolicitud()
            ));

            return $solicitud->getTipoPago() == SolicitudTipoPagoInterface::TIPO_PAGO_MULTIPLE
                ? $this->redirectToRoute('ie#detalle_de_solicitud_multiple', ['id' => $solicitud->getId()])
                : $this->redirectToRoute('ie#detalle_de_solicitud', ['id' => $solicitud->getId()]);
        }

        return $this->render('ie/solicitud/seleccionar_forma_pago.html.twig', [
            'camposClinicos' => $this->normalizer->normalize(
                $camposClinicos->listaCamposClinicosAutorizados(new SolicitudId($solicitud->getId()))
            ),
            'institucion'    => $this->getNormalizeInstitucion($institucion),
            'solicitud'      => $this->getNormalizeSolicitud($solicitud),
        ]);
    }

    #[Route('/solicitudes/{id}/detalle-de-forma-de-pago', name: 'ie#detalle_de_forma_de_pago')]
    public function detalleDeFormaDePago(int $id): Response
    {
        $this->checkPermissionInstitucion();
        $solicitud = $this->checkPermissionSolicitud($id);

        return $this->render('ie/solicitud/detalle_de_forma_de_pago.html.twig', [
            'institucion' => $this->getUser()->getInstitucion(),
            'solicitud'   => $solicitud,
        ]);
    }

    #[Route('/solicitudes/{id}/descargar-referencias-bancarias', name: 'ie#descargar_referencias_bancarias')]
    public function descargarReferenciasBancarias(int $id, GeneradorReferenciaBancariaZIPInterface $generadorReferenciaBancariaZIP): Response
    {
        $this->checkPermissionInstitucion();
        $solicitud = $this->checkPermissionSolicitud($id);

        return $generadorReferenciaBancariaZIP->generarZipResponse($solicitud);
    }

    #[Route('/solicitudes/{id}/cargar-comprobante', name: 'ie#cargar_comprobante')]
    public function cargarComprobante(int $id, Request $request): Response
    {
        $institucion = $this->checkPermissionInstitucion();
        $solicitud   = $this->checkPermissionSolicitud($id);

        $form = $this->createForm(SolicitudComprobantePagoType::class, $solicitud, [
            'action' => $this->generateUrl('ie#cargar_comprobante', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitud->setEstatus(SolicitudInterface::EN_VALIDACION_FOFOE);
            $this->em->persist($form->getData());
            $this->em->flush();
            $this->addFlash('success', 'Se ha guardado correctamente los montos');
            return $this->redirectToRoute('ie#inicio');
        }

        return $this->render('ie/solicitud/cargar_comprobante.html.twig', [
            'institucion' => $this->getNormalizeInstitucion($institucion),
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
        ]);
    }

    #[Route('/solicitudes/{id}/correccion-de-pago-fofoe', name: 'ie#correccion_de_pago_fofoe')]
    public function correccionDePagoFofoe(int $id, Request $request): Response
    {
        $institucion = $this->checkPermissionInstitucion();
        $solicitud   = $this->checkPermissionSolicitud($id);

        $form = $this->createForm(SolicitudComprobantePagoType::class, $solicitud, [
            'action' => $this->generateUrl('ie#correccion_de_pago_fofoe', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitud->setEstatus(SolicitudInterface::EN_VALIDACION_FOFOE);
            $this->em->persist($form->getData());
            $this->em->flush();
            $this->addFlash('success', 'Se ha guardado correctamente los montos');
            return $this->redirectToRoute('ie#inicio');
        }

        return $this->render('ie/solicitud/correccion_de_pago_fofeo.html.twig', [
            'institucion' => $this->getNormalizeInstitucion($institucion),
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
        ]);
    }

    #[Route('/api/solicitud', methods: ['POST'], name: 'ie.solicitud.store')]
    public function store(Request $request, SolicitudManagerInterface $solicitudManager): Response
    {
        $this->checkPermissionInstitucion();

        $form = $this->createForm(CampoClinicoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitud = new Solicitud();
            $usuario   = $this->getUser();
            if ($usuario->getCampus() != null) {
                $solicitud->setCampus($usuario->getCampus());
            }
            return $this->jsonResponse($solicitudManager->create($solicitud));
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/api/solicitud/terminar/{id}', methods: ['POST'], name: 'ie.solicitud.terminar', requirements: ['id' => '\d+'])]
    public function terminar(Request $request, SolicitudManagerInterface $solicitudManager, int $id): Response
    {
        $solicitud = $this->checkPermissionSolicitud($id);

        if ($solicitud->getEstatus() != Solicitud::CREADA) {
            return $this->httpErrorResponse('Solicitud can be finished only if has status "CREADA"');
        }

        $solicitudManager->finalizar($solicitud);
        $this->addFlash('success', "Se ha procesado la solicitud {$solicitud->getNoSolicitud()} con éxito");

        return $this->jsonResponse(['status' => true]);
    }

    private function setFilters(Request $request): array
    {
        return [
            $request->query->get('offset'),
            $request->query->get('search'),
            $request->query->get('tipoPago'),
            $request->query->get('perPage'),
            $request->query->get('orderBy'),
            $request->query->get('estatus'),
        ];
    }

    private function initializeFiltersWithDefaultValues(Request $request): array
    {
        return [
            $request->query->getInt('offset', 1),
            $request->query->get('search'),
            $request->query->get('tipoPago'),
            $request->query->get('perPage', 1),
            $request->query->get('orderBy'),
            $request->query->get('estatus'),
        ];
    }

    private function isRequestedToFilter(mixed ...$filters): bool
    {
        foreach ($filters as $filter) {
            if (isset($filter)) return true;
        }
        return false;
    }

    private function getNormalizeSolicitud(Solicitud $solicitud): array
    {
        return $this->normalizer->normalize($solicitud, 'json', [
            'attributes' => [
                'id', 'noSolicitud', 'estatus', 'fecha', 'formatosFofoeCargados',
                'camposClinicos' => [
                    'id', 'lugaresAutorizados', 'lugaresSolicitados', 'weeks',
                    'displayFechaInicial', 'displayFechaFinal', 'totalTrabajadoresBecados',
                    'horario', 'asignatura', 'monto',
                    'unidad'        => ['nombre'],
                    'carrera'       => ['id'],
                    'cicloAcademico' => ['id', 'nombre'],
                    'convenio'      => [
                        'cicloAcademico' => ['id', 'nombre'],
                        'carrera'        => ['id', 'nombre', 'nivelAcademico' => ['nombre']],
                    ],
                    'observaciones',
                    'montoCarrera'  => [
                        'montoInscripcion', 'montoColegiatura',
                        'carrera'    => ['id', 'nombre', 'nivelAcademico' => ['nombre']],
                        'descuentos' => ['numAlumnos', 'descuentoInscripcion', 'descuentoColegiatura'],
                    ],
                ],
                'montosCarreras' => [
                    'montoInscripcion', 'montoColegiatura',
                    'carrera'    => ['id', 'nombre', 'nivelAcademico' => ['nombre']],
                    'descuentos' => ['numAlumnos', 'descuentoInscripcion', 'descuentoColegiatura'],
                ],
                'observaciones', 'referenciaBancaria', 'monto',
                'pagos' => ['id', 'monto', 'fechaPago', 'comprobantePago', 'requiereFactura'],
            ],
        ]);
    }

    private function getNormalizeInstitucion(Institucion $institucion): array
    {
        return $this->normalizer->normalize($institucion, 'json', [
            'attributes' => ['id', 'nombre', 'rfc'],
        ]);
    }

    private function checkPermissionInstitucion(): Institucion
    {
        $institucion = $this->getUser()->getInstitucion();
        if (!$institucion) {
            $this->createNotFindUserRelationWithInstitucionException();
        }
        return $institucion;
    }

    private function checkPermissionSolicitud(int $id): Solicitud
    {
        $this->checkPermissionInstitucion();
        $solicitud = $this->solicitudRepository->find($id);
        if (!$solicitud) {
            $this->createNotFindSolicitudException($id);
        }
        return $solicitud;
    }

    private function checkPermissionCreateSolCC(CampoClinico $campoClinico): void
    {
        $institucion = $this->checkPermissionInstitucion();

        if ($campoClinico->getConvenio()->getInstitucion()->getId() != $institucion->getId()) {
            throw $this->createAccessDeniedException('NO es posible crear solicitudes con convenios de otras Instituciones');
        }

        $estatus = [SolicitudInterface::CREADA, SolicitudInterface::REGISTRADA];
        if (
            ($campoClinico->getUnidad()->getEsUmae() &&
                $this->solicitudRepository->getSolicitudesByInstitucionAndEstatus($institucion->getId(), $estatus, null, $campoClinico->getDelegacionUmae()->getId())
            ) || (
                !$campoClinico->getUnidad()->getEsUmae() &&
                $this->solicitudRepository->getSolicitudesByInstitucionAndEstatus($institucion->getId(), $estatus, $campoClinico->getDelegacionUmae()->getId())
            )
        ) {
            throw $this->createAccessDeniedException('Operación no permitida. Ya existe una solicitud registrada que aún no ha sido procesada');
        }
    }

    public function getOOADByUMAE(int $ooad_umae): array
    {
        if (in_array($ooad_umae, [35, 36])) return [35, 36];
        if (in_array($ooad_umae, [37, 38])) return [37, 38];
        if (in_array($ooad_umae, [31, 32])) return [31, 32];
        return [$ooad_umae];
    }
}
