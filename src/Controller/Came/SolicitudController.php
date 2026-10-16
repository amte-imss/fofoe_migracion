<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Convenio;
use App\Entity\Institucion;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Entity\Unidad;
use App\Form\Type\Came\ValidarSolicitudCC\ValidarSolicitudCCType;
use App\Form\Type\RegistoMontos\SolicitudOficioMontosType;
use App\Form\Type\SolicitudType;
use App\Form\Type\ValidaMontoCampoClinicoType;
use App\Form\Type\ValidaSolicitudType;
use App\Service\SolicitudManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Handler\DownloadHandler;

class SolicitudController extends DIEControllerController
{
    private const DEFAULT_PERPAGE = 10;

    // Los atributos de serialización se extraen como constantes
    // para no repetirlos entre indexAction e indexApiAction
    private const SOLICITUD_LIST_ATTRIBUTES = [
        'attributes' => [
            'id',
            'fecha',
            'estatus',
            'noSolicitud',
            'estatusCameFormatted',
            'institucion'                => ['id', 'nombre'],
            'camposClinicosSolicitados',
            'camposClinicosAutorizados',
            'formatosFofoeCargados',
        ],
    ];

    private const INSTITUCION_ATTRIBUTES = [
        'attributes' => [
            'id', 'nombre', 'rfc', 'direccion',
            'telefono', 'extension', 'correo',
            'sitioWeb', 'fax', 'representante',
        ],
    ];

    private const UNIDAD_LIST_ATTRIBUTES = [
        'attributes' => ['id', 'nombre', 'claveUnidad'],
    ];

    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    // -------------------------------------------------------------------------
    // Index / listado
    // -------------------------------------------------------------------------

    #[Route('/came/solicitud', name: 'came.solicitud.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $perPage = $request->query->get('perPage', self::DEFAULT_PERPAGE);
        $page    = $request->query->get('page', 1);

        $solicitudes = $this->getSolicitudes($request, $perPage, $page);

        return $this->render('came/solicitud/index.html.twig', [
            'solicitudes' => $this->serializer->normalize(
                $solicitudes['data'], 'json', self::SOLICITUD_LIST_ATTRIBUTES
            ),
            'meta' => [
                'total'   => $solicitudes['total'],
                'perPage' => $perPage,
                'page'    => $page,
            ],
        ]);
    }

    #[Route('/came/api/solicitud', name: 'solicitud.index.json', methods: ['GET'])]
    public function indexApi(Request $request): JsonResponse
    {
        $perPage = $request->query->get('perPage', self::DEFAULT_PERPAGE);
        $page    = $request->query->get('page', 1);

        $solicitudes = $this->getSolicitudes($request, $perPage, $page);

        return $this->jsonResponse([
            'object' => $this->serializer->normalize(
                $solicitudes['data'], 'json', self::SOLICITUD_LIST_ATTRIBUTES
            ),
            'meta' => [
                'total'   => $solicitudes['total'],
                'perPage' => $perPage,
                'page'    => $page,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    #[Route('/came/solicitud/create', name: 'solicitud.create', methods: ['GET'])]
    public function create(Request $request): Response
    {
        [$delegacion, $unidad] = $this->getValidatedDelegacionUnidad();

        $form         = $this->createForm(SolicitudType::class);
        $instituciones = $this->em->getRepository(Institucion::class)->findAllPrivate();
        $unidades      = $this->resolveUnidades($delegacion, $unidad);

        return $this->render('came/solicitud/create.html.twig', [
            'form'         => $form->createView(),
            'instituciones' => $this->serializer->normalize(
                $instituciones, 'json', self::INSTITUCION_ATTRIBUTES
            ),
            'unidades' => $this->serializer->normalize(
                $unidades, 'json', self::UNIDAD_LIST_ATTRIBUTES
            ),
        ]);
    }

    #[Route('/came/api/solicitud', name: 'solicitud.store', methods: ['POST'])]
    public function store(Request $request, SolicitudManagerInterface $solicitudManager): JsonResponse
    {
        $result = $solicitudManager->create(new Solicitud());
        return $this->jsonResponse($result);
    }

    #[Route('/came/solicitud/{id}/edit', name: 'solicitud.edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(Request $request, int $id): Response
    {
        [$delegacion, $unidad] = $this->getValidatedDelegacionUnidad();

        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::CREADA, 'came.solicitud.index');
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $instituciones = $this->em->getRepository(Institucion::class)->findAllPrivate();
        $unidades      = $this->resolveUnidades($delegacion, $unidad);
        $form          = $this->createForm(SolicitudType::class);

        return $this->render('came/solicitud/edit.html.twig', [
            'form'         => $form->createView(),
            'instituciones' => $this->serializer->normalize(
                $instituciones, 'json', self::INSTITUCION_ATTRIBUTES
            ),
            'solicitud' => $this->serializer->normalize($solicitud, 'json', ['attributes' => [
                'id',
                'campoClinicos' => [
                    'id', 'asignatura', 'promocion',
                    'convenio' => [
                        'cicloAcademico' => ['id', 'nombre'],
                        'id', 'vigencia', 'vigenciaFormatted', 'label',
                        'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                    ],
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
            ]]),
            'unidades' => $this->serializer->normalize(
                $unidades, 'json', ['attributes' => ['id', 'nombre']]
            ),
        ]);
    }

    #[Route('/came/api/solicitud/{id}', name: 'solicitud.update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(Request $request, SolicitudManagerInterface $solicitudManager, int $id): JsonResponse
    {
        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::CREADA);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $form = $this->createForm(SolicitudType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->jsonResponse($solicitudManager->update($form->getData()));
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/came/solicitud/{id}', name: 'solicitud.show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $solicitud = $this->checkPermissionSolicitud($id);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $convenios = $this->em->getRepository(Convenio::class)->getAllBySolicitud($solicitud->getId());

        return $this->render('came/solicitud/show.html.twig', [
            'solicitud' => $this->serializer->normalize($solicitud, 'json', ['attributes' => [
                'id', 'noSolicitud', 'estatusCameFormatted', 'tipoPago',
                'fechaComprobanteFormatted', 'validateOficioMontos', 'motiveOficioMontos',
                'fechaComprobante', 'estatus', 'validado',
                'institucion'              => ['id', 'nombre'],
                'camposClinicosSolicitados', 'camposClinicosAutorizados',
                'campoClinicos' => [
                    'id', 'asignatura', 'promocion', 'formatoFofoeFileName',
                    'cicloAcademico'       => ['id', 'nombre'],
                    'validateFormatoFofoe', 'motiveFormatoFofoe',
                    'convenio' => [
                        'cicloAcademico' => ['id', 'nombre'],
                        'id', 'vigencia', 'vigenciaFormatted', 'label', 'numero',
                        'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                    ],
                    'lugaresSolicitados', 'lugaresAutorizados', 'horario',
                    'unidad'             => ['id', 'nombre'],
                    'trabajadoresBecados' => ['matricula'],
                    'fechaInicial', 'fechaFinal', 'referenciaBancaria',
                    'fechaInicialFormatted', 'fechaFinalFormatted',
                    'estatus'            => ['id', 'nombre'],
                ],
                'pago'  => ['id', 'comprobantePago', 'validado', 'fechaPago', 'fechaPagoFormatted', 'referenciaBancaria', 'factura' => ['fechaFacturacion', 'id', 'fechaFacturacionFormatted']],
                'pagos' => ['id', 'comprobantePago', 'validado', 'fechaPago', 'fechaPagoFormatted', 'referenciaBancaria', 'factura' => ['fechaFacturacion', 'id', 'fechaFacturacionFormatted']],
            ]]),
            'convenios' => $this->serializer->normalize($convenios, 'json', ['attributes' => [
                'cicloAcademico' => ['id', 'nombre'],
                'numero', 'ciclosAcademicosFormatted',
                'id', 'vigencia', 'vigenciaFormatted', 'label',
                'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
            ]]),
        ]);
    }

    #[Route('/came/api/solicitud/{id}', name: 'solicitud.delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $solicitud = $this->checkPermissionSolicitud($id);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $this->em->remove($solicitud);
        $this->em->flush();

        return new JsonResponse(['status' => true, 'message' => 'Solicitud Eliminada con éxito']);
    }

    #[Route('/came/api/solicitud/terminar/{id}', name: 'solicitud.terminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function terminar(Request $request, SolicitudManagerInterface $solicitudManager, int $id): JsonResponse
    {
        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::CREADA);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $solicitudManager->finalizar($solicitud, $this->getUser());
        $this->addFlash('success', "Se ha procesado la solicitud {$solicitud->getNoSolicitud()} con éxito");

        return $this->jsonResponse(['status' => true]);
    }

    // -------------------------------------------------------------------------
    // Validar montos
    // -------------------------------------------------------------------------

    #[Route('/came/solicitud/{id}/validar_montos', name: 'solicitud.validar_montos', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function validarMontos(Request $request, int $id): Response
    {
        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::EN_VALIDACION_DE_MONTOS_CAME, 'came.solicitud.index');
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $form = $this->createFormValidaMontos($solicitud);

        return $this->render('came/solicitud/valida_montos.html.twig', [
            'form'      => $form->createView(),
            'solicitud' => $this->serializer->normalize($solicitud, 'json', ['attributes' => [
                'id', 'noSolicitud', 'estatusCameFormatted',
                'documento', 'urlArchivo',
                'institucion' => ['id', 'nombre'],
                'camposClinicos' => [
                    'id', 'lugaresAutorizados', 'totalTrabajadoresBecados',
                    'observaciones', 'horario', 'asignatura', 'monto',
                    'estatus'     => ['nombre'],
                    'displayFechaInicial', 'displayFechaFinal',
                    'unidad'      => ['nombre'],
                    'carrera'     => ['id'],
                    'montoCarrera' => [
                        'id', 'montoInscripcion', 'montoColegiatura',
                        'carrera'    => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                        'descuentos' => ['numAlumnos', 'descuentoInscripcion', 'descuentoColegiatura'],
                    ],
                ],
                'montosCarreras' => [
                    'id', 'montoInscripcion', 'montoColegiatura',
                    'carrera'    => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                    'descuentos' => ['numAlumnos', 'descuentoInscripcion', 'descuentoColegiatura'],
                ],
            ]]),
        ]);
    }

    #[Route('/came/api/solicitud/validar_montos/{id}', name: 'solicitud.store_validar_montos', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function validarMontosStore(Request $request, SolicitudManagerInterface $solicitudManager, int $id): JsonResponse
    {
        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::EN_VALIDACION_DE_MONTOS_CAME);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $form = $this->createFormValidaMontos($solicitud);
        $form->handleRequest($request);

        $originalDescuentos = [];
        foreach ($solicitud->getMontosCarreras() as $monto) {
            $originalDescuentos[$monto->getId()] = [];
            foreach ($monto->getDescuentos() as $descuento) {
                if ($descuento->getId()) {
                    $originalDescuentos[$monto->getId()][$descuento->getId()] = $descuento;
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitudData = $request->request->all('solicitud');
            $result = $solicitudManager->validarMontos(
                $form->getData(),
                $this->getDataMontosForm($form, $solicitud),
                isset($solicitudData['validado']),
                $this->getUser(),
                $originalDescuentos
            );

            if ($result['status']) {
                $this->addFlash('success', "Se ha procesado la validación de montos de la solicitud {$solicitud->getNoSolicitud()} con éxito");
            }

            return $this->jsonResponse($result);
        }

        return $this->jsonErrorResponse($form);
    }

    // -------------------------------------------------------------------------
    // Oficio de montos
    // -------------------------------------------------------------------------

    #[Route('/came/solicitud/{solicitud_id}/oficio', name: 'came.solicitud.oficio_montos', requirements: ['solicitud_id' => '\d+'], methods: ['GET'])]
    public function downloadOficioMontos(int $solicitud_id,
                                         #[Autowire(service: 'vich_uploader.download_handler')]
                                         DownloadHandler $downloadHandler): Response
    {
        $solicitud = $this->findSolicitudOr404($solicitud_id);
        return $this->downloadHandler->downloadObject($solicitud, 'urlArchivoFile');
    }

    #[Route('/came/solicitud/{solicitud_id}/upload-oficio-montos', name: 'came.solicitud.upload-oficio-montos', methods: ['POST'])]
    public function uploadOficioMontos(Request $request, int $solicitud_id): JsonResponse
    {
        $solicitud = $this->findSolicitudOr404($solicitud_id);
        $form      = $this->createForm(SolicitudOficioMontosType::class, $solicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->em->persist($data);
            $this->em->flush();
            $this->em->clear();

            $solicitud = $this->em->getRepository(Solicitud::class)->find($solicitud_id);
            $solicitud->setValidateOficioMontos(null);
            $this->em->persist($solicitud);
            $this->em->flush();

            return new JsonResponse([
                'status' => true,
                'data'   => [
                    'id'                   => $data->getId(),
                    'validateOficioMontos' => $data->getValidateOficioMontos(),
                ],
            ]);
        }

        return $this->jsonErrorResponse($form);
    }

    // -------------------------------------------------------------------------
    // Emails (preview)
    // -------------------------------------------------------------------------

    #[Route('/came/solicitud/{solicitud_id}/email/montos_invalidos', name: 'solicitud.email.montos_invalidos', methods: ['GET'])]
    public function showMailTemplate(int $solicitud_id = 1): Response
    {
        $solicitud = $this->findSolicitudOr404($solicitud_id);
        return $this->render('emails/came/montos_invalidos.html.twig', [
            'solicitud' => $solicitud,
            'came'      => $this->getUser(),
        ]);
    }

    #[Route('/came/solicitud/{solicitud_id}/email/bienvenida', name: 'solicitud.email.bienvenida', methods: ['GET'])]
    public function showMailBienvenidaTemplate(int $solicitud_id = 1): Response
    {
        $solicitud = $this->findSolicitudOr404($solicitud_id);
        return $this->render('emails/came/institucion_bienvenida.html.twig', [
            'solicitud' => $solicitud,
            'password'  => '',
            'came'      => $this->getUser(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Obtiene delegación y unidad validando que al menos una exista,
     * lanza AccessDeniedException si ninguna está disponible.
     */
    private function getValidatedDelegacionUnidad(): array
    {
        $delegacion = $this->getUserDelegacionId();
        $unidad     = $this->getUserUnidadId();

        if (is_null($delegacion) && is_null($unidad)) {
            throw $this->createAccessDeniedException();
        }

        return [$delegacion, $unidad];
    }

    /**
     * Resuelve la lista de unidades según si el usuario opera
     * por delegación o por unidad directa.
     */
    private function resolveUnidades(mixed $delegacion, mixed $unidad): array
    {
        if ($delegacion && $this->isUserDelegacionActivated()) {
            return $this->em
                ->getRepository(Unidad::class)
                ->getAllUnidadesByDelegacion($delegacion, false);
        }

        $unidadE = $this->em->getRepository(Unidad::class)->findOneBy(['id' => $unidad]);
        return $unidadE ? [$unidadE] : [];
    }

    /**
     * Lógica compartida entre index() e indexApi() para obtener solicitudes.
     */
    private function getSolicitudes(Request $request, mixed $perPage, mixed $page): array
    {
        [$delegacion, $unidad] = $this->getValidatedDelegacionUnidad();
        $filters = $request->query->all();
        $repo    = $this->em->getRepository(Solicitud::class);

        return $delegacion && $this->isUserDelegacionActivated()
            ? $repo->getAllSolicitudesByDelegacion($delegacion, $perPage, $page, $filters)
            : $repo->getAllSolicitudesByUnidad($unidad, $perPage, $page, $filters);
    }

    private function findSolicitudOr404(int $id): Solicitud
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException("No encontrada la solicitud con id: $id");
        }

        return $solicitud;
    }

    private function checkPermissionSolicitud(int $id, string $checkEstatus = '', ?string $redirectRoute = null): Solicitud|Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);

        if (!$solicitud) {
            $this->addFlash('danger', 'No existe la solicitud indicada');
            return $this->redirectToRoute('came.solicitud.index');
        }

        if (!$this->isGrantedUserAccessToSolicitud($solicitud)) {
            $this->addFlash('danger', 'No puedes modificar una solicitud de otra OOAD / UMAE');
            return $this->redirectToRoute('came.solicitud.index');
        }

        if ($checkEstatus !== '' && $solicitud->getEstatus() != $checkEstatus) {
            if ($redirectRoute) {
                $this->addFlash('danger', 'No es posible procesar la solicitud indicada');
                return $this->redirectToRoute($redirectRoute);
            }
            return $this->httpErrorResponse('No puedes modificar la solicitud ' . $solicitud->getNoSolicitud());
        }

        return $solicitud;
    }

    private function createFormValidaMontos(Solicitud $solicitud): FormInterface
    {
        $form = $this->createForm(ValidaSolicitudType::class, $solicitud);

        /** @var CampoClinico $campo */
        foreach ($solicitud->getCamposClinicos() as $campo) {
            if ($campo->getLugaresAutorizados() <= 0) {
                continue;
            }
            $form->add('campo_' . $campo->getId(), ValidaMontoCampoClinicoType::class, ['mapped' => false]);
            $form->get('campo_' . $campo->getId())->setData($campo);
        }

        return $form;
    }

    private function getDataMontosForm(FormInterface $form, Solicitud $solicitud): array
    {
        $montos = [];

        /** @var CampoClinico $campo */
        foreach ($solicitud->getCamposClinicos() as $campo) {
            if ($campo->getLugaresAutorizados() <= 0) {
                continue;
            }
            $montos[$campo->getId()] = $form->get('campo_' . $campo->getId());
        }

        return $montos;
    }
}
