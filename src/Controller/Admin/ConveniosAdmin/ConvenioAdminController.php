<?php

namespace App\Controller\Admin\ConveniosAdmin;

use App\Controller\DIEControllerController;
use App\Entity\CicloAcademico;
use App\Entity\Convenio;
use App\Entity\ConvenioCicloAcademico;
use App\Entity\ConvenioDelegacion;
use App\Entity\ConvenioEliminado;
use App\Entity\Delegacion;
use App\Event\ConvenioEvent;
use App\Form\ConvenioAdminType;
use App\Form\Type\Institucion\Type\Convenios\CargaConveniosType;
use App\Normalizer\ConvenioNormalizer;
use App\Normalizer\UsuarioNormalizer;
use App\Repository\CarreraRepository;
use App\Repository\CicloAcademicoRepository;
use App\Repository\ConvenioRepository;
use App\Repository\ConvenioRepositoryInterface;
use App\Repository\DisciplinaRepository;
use App\Repository\InstitucionRepository;
use App\Repository\NivelAcademicoRepository;
use App\Service\ConvenioService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConvenioAdminController extends DIEControllerController
{
    private const int LEGACY_PAGE_LIMIT = 10;
    private const int LEGACY_JUMP_LIMIT = 3;
    private const int UPLOAD_SIZE_LIMIT_MB = 5;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly PaginatorInterface $paginator,
        private readonly NormalizerInterface $normalizer,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin', name: 'admin.conveniosadmin.conveniosadmin.index', methods: ['GET', 'POST'])]
    public function index(Request $request, ConvenioRepositoryInterface $convenioRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $agreementNormalizer = new ConvenioNormalizer();

            $filters = [
                'search' => (string) $this->input($request, 'search', ''),
                'type' => (string) $this->input($request, 'type', 'all'),
                'startDate' => (string) $this->input($request, 'startDate', ''),
                'endDate' => (string) $this->input($request, 'endDate', ''),
            ];

            $pagination = $convenioRepository->paginateAdminConvenios(
                $filters,
                (int) $this->input($request, 'page', 1),
                (int) $this->input($request, 'perPage', 20),
            );
            $pagination['data'] = $agreementNormalizer->serializarData($pagination['data']);

            return new JsonResponse($pagination);
        }

        return $this->render('admin/convenioadmin/index.html.twig', [
            'usuario' => $this->getUser(),
            'is_convenios_observer' => $this->isGranted('ROLE_CONVENIOS_OBSERVER'),
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadminold', name: 'admin.conveniosadmin.conveniosadmin.indexold', methods: ['GET', 'POST'])]
    public function indexOld(Request $request, ConvenioRepository $convenioRepository): Response
    {
        $page = (int) ($this->input($request, 'page') ?: 1);
        $orderBy = (string) ($this->input($request, 'orderBy') ?: '');
        $requestedDirection = (string) ($this->input($request, 'orderDirection') ?: '');
        $orderDirection = $requestedDirection !== '' ? $requestedDirection : 'desc';
        $search = $this->input($request, 'convenioToSearch');

        $agreements = $orderBy !== ''
            ? $convenioRepository->getAllConvenios(false, '', '', $orderDirection, $orderBy)
            : $convenioRepository->getAllConvenios(false);

        $totalRecords = (int) $convenioRepository->getCountConvenios(false, $search);
        $totalPages = (int) ceil($totalRecords / self::LEGACY_PAGE_LIMIT);

        if (!empty($agreements)) {
            $agreements = $this->paginator->paginate($agreements, $page, self::LEGACY_PAGE_LIMIT);
        }

        $orderDirection = match ($orderDirection) {
            'asc' => 'desc',
            'desc' => 'asc',
            default => $orderDirection,
        };

        return $this->render('admin/convenioadmin/index.html.twig', [
            ...$this->legacyPaginationParams($page, $totalPages, $totalRecords),
            'convenios' => !empty($agreements) ? $agreements : null,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection,
            'usuario' => $this->getUser(),
            'errorMessage' => '',
            'successMessage' => '',
            'orderDirectionP' => $requestedDirection,
            'criterioDeBusqueda' => $search ?: '',
            'convenioToSearch' => $search ?: '',
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/search', name: 'admin.conveniosadmin.conveniosadmin.search', methods: ['GET', 'POST'])]
    public function conveniosAdminSearching(Request $request, ConvenioRepository $convenioRepository): Response
    {
        $page = (int) ($this->input($request, 'page') ?: 1);
        $orderBy = (string) ($this->input($request, 'orderBy') ?: '');
        $orderDirection = (string) ($this->input($request, 'orderDirection') ?: '');
        $search = $this->input($request, 'convenioToSearch');

        $agreements = $convenioRepository->getAllConvenios(false, $search);
        $totalRecords = (int) $convenioRepository->getCountConvenios(false, $search);
        $totalPages = (int) ceil($totalRecords / self::LEGACY_PAGE_LIMIT);

        if (!empty($agreements)) {
            $agreements = $this->paginator->paginate($agreements, $page, self::LEGACY_PAGE_LIMIT);
        }

        return $this->render('admin/convenioadmin/index.html.twig', [
            ...$this->legacyPaginationParams($page, $totalPages, $totalRecords),
            'convenios' => !empty($agreements) ? $agreements : null,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection,
            'usuario' => $this->getUser(),
            'orderDirectionP' => $orderDirection,
            'errorMessage' => '',
            'successMessage' => '',
            'criterioDeBusqueda' => $search,
            'convenioToSearch' => $search ?: '',
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/{id}/deleted', name: 'admin.conveniosadmin.conveniosadmin.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function convenioBorrado(int $id, ConvenioRepository $convenioRepository): JsonResponse
    {
        $agreement = $convenioRepository->find($id);
        $this->dispatcher->dispatch(new ConvenioEvent($agreement), ConvenioEvent::CONVENIO_ELIMINADO);

        if ($agreement !== null) {
            $deletedRecord = new ConvenioEliminado();
            $deletedRecord->setConvenioInfo(json_encode($agreement->toArray(), JSON_UNESCAPED_UNICODE));
            $deletedRecord->setUsuario($this->getUser());
            $deletedRecord->setFuica($agreement->getFuica());
            $deletedRecord->setNumero($agreement->getNumero());
            $deletedRecord->setRfc($agreement->getRfc());
            $this->em->persist($deletedRecord);

            $this->em->remove($agreement);
            $this->em->flush();
        }

        $deleteResult = $convenioRepository->deleteConvenioCame($id);

        if (!empty($deleteResult)) {
            return new JsonResponse([
                'status' => false,
                'message' => 'No ha sido posible borrar el convenio, por favor intenta de nuevo.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => true,
            'message' => 'El convenio ha sido borrado de forma exitosa.',
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/new', name: 'admin.conveniosadmin.conveniosadmin.new', methods: ['GET'])]
    public function new(): Response
    {
        $userNormalizer = new UsuarioNormalizer();

        return $this->render('admin/convenioadmin/new.html.twig', [
            'usuario' => $this->getUser(),
            'usuarioJson' => $userNormalizer->serialize($this->getUser()),
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/edit/{id}', name: 'admin.conveniosadmin.conveniosadmin.edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(Convenio $convenio): Response
    {
        $agreementNormalizer = new ConvenioNormalizer();

        return $this->render('admin/convenioadmin/edit.html.twig', [
            'usuario' => $this->getUser(),
            'convenio' => $agreementNormalizer->serializer($convenio),
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/old_edit', name: 'admin.conveniosadmin.conveniosadmin.old_edit', methods: ['GET', 'POST'])]
    public function oldEdit(
        Request $request,
        ConvenioRepository $convenioRepository,
        InstitucionRepository $institucionRepository,
        ConvenioService $convenioService,
    ): Response {
        $params = $request->request->all('appbundle_convenio_admin');

        /** @var Convenio $agreement */
        $agreement = $convenioRepository->find($this->input($request, 'id'));

        $form = $this->createForm(ConvenioAdminType::class, $agreement);
        $form->handleRequest($request);

        if ($params === []) {
            $form->get('numero')->setData($agreement->getId());
        } else {
            if (isset($params['institucion'])) {
                $institutions = $institucionRepository->getInstitutionById($params['institucion']);
                if (!empty($institutions)) {
                    $agreement->setInstitucion($institutions[0]);
                }
            }

            if (isset($params['convenio_general']) && $agreement->getTipo() === 'Específico') {
                $generalAgreements = $convenioRepository->getConvenioById((int) $params['convenio_general']);
                if (!empty($generalAgreements)) {
                    $agreement->setConvenioGeneral($generalAgreements[0]);
                    $agreement->setGeneral($generalAgreements[0]);
                    $form->get('convenio_general')->setData($generalAgreements[0]);
                }
            }

            $signatureDate = !empty($agreement->getFechaFirma())
                ? $agreement->getFechaFirma()
                : ($params['fecha_firma'] ?? null);

            if ($agreement->getIsCamex()) {
                $agreement->setTipoCame('Local');
            }

            $agreement->setFechaVenceConvenio(
                $convenioRepository->getFechaVigenciaCalculada($agreement->getVigenciaText(), $signatureDate)
            );

            $currentFuica = (string) $agreement->getFuica();
            if ($currentFuica === ''
                || str_contains(mb_strtolower($currentFuica), 'xxxx')
                || strlen($currentFuica) !== 14
            ) {
                $fuica = $convenioRepository->getCalculatedFuica(
                    $agreement->getRfc(),
                    $agreement->getTipo(),
                    $signatureDate,
                    $agreement->getVigenciaText(),
                    $agreement->getId(),
                );
                $agreement->setFuica($fuica);
                $form->get('fuica')->setData($fuica);
            }

            if (!empty($params['descrip_otraDisciplina'])) {
                $convenioService->storeDisciplina($agreement, $params['descrip_otraDisciplina']);
            }
        }

        $agreement->setNumero($agreement->getId());

        if ($form->isSubmitted()) {
            $this->em->persist($agreement);
            $this->em->flush();

            return $this->redirectToRoute('admin.conveniosadmin.conveniosadmin.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/convenioadmin/edit.html.twig', [
            'form' => $form->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/show', name: 'admin.conveniosadmin.conveniosadmin.show', methods: ['GET', 'POST'])]
    public function show(Request $request, ConvenioRepository $convenioRepository): Response
    {
        return $this->render('admin/convenioadmin/show.html.twig', [
            'convenio' => $convenioRepository->find($this->input($request, 'id')),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/carga', name: 'admin.conveniosadmin.conveniosadmin.carga', methods: ['GET', 'POST'])]
    public function cargaMasiva(Request $request, ConvenioRepository $convenioRepository): Response
    {
        $message = '';
        $successMessage = '';

        $form = $this->createForm(CargaConveniosType::class);
        $form->handleRequest($request);

        $uploadedFiles = $request->files->all('appbundle_convenio_carga');

        if ($uploadedFiles !== []) {
            $uploadedFile = $uploadedFiles['file'] ?? null;

            if ($uploadedFile === null) {
                $message = 'El archivo para ejecutar carga masiva de convenios, no debe ser nulo. Por favor verificalo e intenta de nuevo.';
            } else {
                $mimeType = $uploadedFile->getMimeType();
                $size = $convenioRepository->conversorOfFileSize($uploadedFile->getSize());

                if ($mimeType && $size <= self::UPLOAD_SIZE_LIMIT_MB) {
                    $rows = [];
                    $handle = fopen($uploadedFile->getPathname(), 'r');
                    if ($handle !== false) {
                        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
                            $rows[] = $row;
                        }
                        fclose($handle);
                    }

                    // TODO: crearConvenios() is not defined in DIEControllerController; pending migration
                    $result = $this->crearConvenios($rows);

                    if ($result['status'] === true) {
                        return $this->render('admin/convenioadmin/cargaMasiva.html.twig', [
                            'form' => $form->createView(),
                            'usuario' => $this->getUser(),
                            'errorMessage' => '',
                            'successMessage' => $successMessage,
                        ]);
                    }

                    $message = 'Hubo un problema al momento de guardar los convenios: ';
                    if (isset($result['content']['error'])) {
                        $message .= $result['content']['error'];
                    } else {
                        $errors = [];
                        foreach ($result['content'] as $agreementData) {
                            if (!empty($agreementData['error'])) {
                                $errors[] = 'El convenio: ' . $agreementData['nombre'] . ' falló en: ' . $agreementData['error'];
                            }
                        }
                        $message = $errors === [] ? '' : $message . implode(' ', $errors);
                    }
                } elseif (!$mimeType) {
                    $message = 'El archivo que has cargado o subido, no es válido, por favor sube un archivo de tipo CSV(separado por comas).';
                } else {
                    $message = 'El archivo que has cargado o subido, no es válido, excede el limite de tamaño correspondiente('
                        . self::UPLOAD_SIZE_LIMIT_MB . 'MB) por favor sube un archivo de tamaño menor.';
                }
            }
        }

        return $this->render('admin/convenioadmin/cargaMasiva.html.twig', [
            'form' => $form->createView(),
            'usuario' => $this->getUser(),
            'errorMessage' => $message,
            'successMessage' => $successMessage,
        ]);
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/downloadcsv', name: 'admin.conveniosadmin.conveniosadmin.downloadcsv', methods: ['GET', 'POST'])]
    public function downloadCsv(): Response
    {
        $rows = [
            ['R.F.C. de Convenio', 'Razon Social', 'Nombre Comercial', 'Institucion', 'Convenio General',
                'OOAD', 'Sector', 'Tipo', 'Objetivo de la Colaboracion', 'Motivo de la Colaboracion', 'Nivel o Grado Academico',
                'Disciplina', 'Carrera', 'Nombre del Programa', 'Fecha de Firma', 'Vigencia(anios)', 'Fecha de Vigencia de Convenio',
                'Fecha de Emision OTA', 'Fecha Vigencia de Emision OTA', 'Fecha de Emision RVOE', 'Fecha Vigencia de Emision RVOE',
                'Fecha de Emision Acreditacion (COMAEM u otros)', 'Fecha Vigencia de Emision Acreditacion (COMAEM u otros)',
                'Cargo Firmante IMSS 1ro.', 'Nombre Firmante IMSS 1ro.', 'Cargo Institucion 1ro.', 'Nombre Institucion 1ro.',
                'Cargo Firmante IMSS 2do.', 'Nombre Firmante IMSS 2do.', 'Cargo Institucion 2do.', 'Nombre Institucion 2do.'],
            ['GAGC880502G44', 'Patito SA de CV', 'Un convenio came de prueba', 'Instituto Dr. Carlos Coqui - Campus Zacatecas',
                'Convenio General Campus Zacatecas', 'Edo Mex Pte', 'Publico', 'General', 'Nunc a suscipit ...', 'Internado medico',
                'Educacion Continua', 'Medicina', 'Ingenieria Procesos de Extraccion de Citricos', 'Psicologia Organizacional',
                '25-04-2024', '13 años', '24-04-2024', '24-04-2024', '02-05-2024', '11-05-2024', '24-04-2024', '02-05-2024', '11-05-2024',
                'Coordinacion Firmante 3', 'nobmre imss 1', 'cargo firmante 1', 'nombre firmante 1', 'Coordinacion Firmante 1',
                'nobmre imss 2', 'cargo firmante 2', 'nombre firmante 2'],
        ];

        $handle = fopen('php://temp', 'w+');
        foreach ($rows as $fields) {
            fputcsv($handle, $fields, escape: '\\');
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'convenios.csv'),
        ]);
    }

    #[Route('/admin/conveniosadmin/getconveniogral', name: 'admin.conveniosadmin.getconveniogral', methods: ['GET', 'POST'])]
    public function getConveniosGenerales(Request $request, InstitucionRepository $institucionRepository): Response
    {
        $result = ['status' => false, 'data' => null];

        $institutions = $institucionRepository->getInstitucionPorRFCV2($this->input($request, 'rfc'));

        $result['instituciones'] = $this->normalizer->normalize($institutions, 'json', [
            'attributes' => ['id', 'nombre'],
        ]);

        if (count($institutions) > 0) {
            $result['razonSocial'] = $institutions[0]->getRazonSocial();
            $result['nombreComercial'] = $institutions[0]->getNombre();
            $result['status'] = true;
        }

        return new Response(json_encode($result));
    }

    #[Route('/admin/conveniosadmin/old_getconveniogral', name: 'admin.conveniosadmin.old.getconveniogral', methods: ['GET', 'POST'])]
    public function getConveniosGeneralesOld(
        Request $request,
        InstitucionRepository $institucionRepository,
        ConvenioRepository $convenioRepository,
    ): Response {
        $institutions = $institucionRepository->getInstitucionPorRFC($this->input($request, 'rfc'));
        $agreements = $convenioRepository->getConveniosPorRfcDeInstitucion($institutions);

        return new Response(json_encode([
            'status' => !empty($agreements),
            'data' => $agreements,
            'instituciones' => $convenioRepository->getListaInstituciones($institutions),
            'razonSocial' => $convenioRepository->getRazonSocialInstitucion($institutions),
        ]));
    }

    #[Route('/admin/conveniosadmin/getcarrera', name: 'admin.conveniosadmin.getcarrera', methods: ['GET', 'POST'])]
    public function getCarrera(Request $request, CarreraRepository $carreraRepository): Response
    {
        $career = $carreraRepository->find($this->input($request, 'selectedId'));
        $isOther = str_contains(mb_strtolower((string) $career?->getNombre()), 'otra');

        return new Response(json_encode(['status' => $isOther ? 'si' : 'no']));
    }

    #[Route('/admin/conveniosadmin/getmotivogeneral', name: 'admin.conveniosadmin.getmotivogeneral', methods: ['GET', 'POST'])]
    public function getMotivoColaboracionGeneral(CicloAcademicoRepository $cicloAcademicoRepository): Response
    {
        $generalCycle = $cicloAcademicoRepository->searchOneByNombre('General');

        return new Response(json_encode([
            'status' => !empty($generalCycle) ? 'si' : 'no',
            'data' => $generalCycle?->getId(),
        ]));
    }

    #[Route('/admin/conveniosadmin/getnivelacademico', name: 'admin.conveniosadmin.getnivelacademico', methods: ['GET', 'POST'])]
    public function getNivelAcademico(Request $request, NivelAcademicoRepository $nivelAcademicoRepository): Response
    {
        $result = ['status' => 'no', 'data' => null];
        $academicLevel = $nivelAcademicoRepository->find($this->input($request, 'selectedId'));

        if ($academicLevel !== null) {
            $result['status'] = 'si';
            if (str_contains(mb_strtolower((string) $academicLevel->getNombre()), 'otro')) {
                $result['data'] = 'true';
            }
        }

        return new Response(json_encode($result));
    }

    #[Route('/admin/conveniosadmin/getdisciplina', name: 'admin.conveniosadmin.getdisciplina', methods: ['GET', 'POST'])]
    public function getDisciplina(Request $request, DisciplinaRepository $disciplinaRepository): Response
    {
        $result = ['status' => 'no', 'data' => null];
        $discipline = $disciplinaRepository->find($this->input($request, 'selectedId'));

        if ($discipline !== null) {
            $result['status'] = 'si';
            if (str_contains(mb_strtolower((string) $discipline->getNombre()), 'otra')) {
                $result['data'] = 'true';
            }
        }

        return new Response(json_encode($result));
    }

    #[Route('/admin/conveniosadmin/conveniosadmin/exportar', name: 'admin.conveniosadmin.conveniosadmin.exportar', methods: ['GET', 'POST'])]
    public function exportarConvenios(Request $request, ConvenioRepository $convenioRepository): Response
    {
        $filters = [
            'search' => (string) $this->input($request, 'search', ''),
            'type' => (string) $this->input($request, 'type', 'all'),
        ];

        $agreements = $convenioRepository->getAdminConvenios($filters)
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();

        if (empty($agreements)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $headers = [
            'Numero de Convenio', 'R.F.C. de Convenio', 'Razon Social', 'Nombre Comercial', 'Institucion',
            'Convenio General', 'OOAD', 'Sector', 'Tipo', 'Objetivo de la Colaboracion', 'Ciclos Académicos',
            'Nivel o Grado Academico', 'Disciplina', 'Carrera', 'Nombre del Programa', 'Fecha firma de Convenio',
            'Vigencia de Convenio', 'Vencimiento del Convenio', 'Emisión OTA', 'Vencimiento OTA', 'Emisión RVOE',
            'Vencimiento RVOE', 'Emisión COMAEM', 'Vencimiento COMAEM', 'Cargo Firmante IMSS 1ro.',
            'Nombre Firmante IMSS 1ro.', 'Cargo Institucion 1ro.', 'Nombre Institucion 1ro.',
            'Cargo Firmante IMSS 2do.', 'Nombre Firmante IMSS 2do.', 'Cargo Institucion 2do.',
            'Nombre Institucion 2do.', 'Fuica', 'URL de documento', 'Creado por OOAD/UMAE/NIVEL CENTRAL',
            'Fecha de Carta de Intención',
        ];

        $upper = static fn (mixed $value): string => mb_strtoupper((string) $value);
        $safeSignerName = static function (callable $getter) use ($upper): string {
            try {
                return $upper($getter()?->getNombre());
            } catch (\Exception) {
                return '';
            }
        };

        $rows = [];
        /** @var Convenio $agreement */
        foreach ($agreements as $agreement) {
            $rows[] = [
                $upper($agreement->getNumero()),
                $upper($agreement->getRfc()),
                $upper($agreement->getRazonSocial()),
                $upper($agreement->getNombre()),
                $upper($agreement->getInstitucion()?->getNombre()),
                $upper($agreement->getConvenioGeneral()?->getNombre()),
                $upper($agreement->getConvenioDelegacionesFormatted()),
                $upper($agreement->getSector()),
                $upper($agreement->getTipo()),
                $upper($agreement->getObjetivoColaboracion()),
                $upper($agreement->getCiclosAcademicosFormatted()),
                $upper($agreement->getNivelAcademico()?->getNombre()),
                $upper($agreement->getDisciplina()?->getNombre()),
                $upper($agreement->getCarrera()?->getNombre()),
                $upper($agreement->getNombrePrograma()),
                $upper($agreement->getFechaFirmaFormatted()),
                $upper($agreement->getVigenciaTextFormatted()),
                $upper($agreement->getFechaVenceConvenioFormatted()),
                $upper($agreement->getFechaOtaFormatted()),
                $upper($agreement->getFechaVenceOtaFormatted()),
                $upper($agreement->getFechaRvoeFormatted()),
                $upper($agreement->getFechaVenceRvoeFormatted()),
                $upper($agreement->getFechaEmiteComaemFormatted()),
                $upper($agreement->getFechaVenceComaemFormatted()),
                $safeSignerName(static fn () => $agreement->getCargoFirmaIdFirst()),
                $upper($agreement->getRecibeSignfirst()),
                $upper($agreement->getCargoEmiteSignfirst()),
                $upper($agreement->getNombreEmiteSignfirst()),
                $safeSignerName(static fn () => $agreement->getCargoFirmaIdSecond()),
                $upper($agreement->getRecibeSignsecond()),
                $upper($agreement->getCargoEmiteSignsecond()),
                $upper($agreement->getNombreEmiteSignsecond()),
                $upper($agreement->getFuica()),
                $upper($agreement->getUrlConvenio()),
                match (true) {
                    $agreement->isUmae() => 'UMAE',
                    (bool) $agreement->getIsCamex() => 'OOAD',
                    default => 'NIVEL CENTRAL',
                },
                $agreement->getFechaCartaIntencion()?->format('d-m-Y') ?? '',
            ];
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($this->getUser()?->getUserIdentifier() ?? '')
            ->setTitle('Convenios CAME')
            ->setSubject('Lista de Exportación de Convenios')
            ->setDescription('Contiene la lista de Convenios Seleccionados para Exportar.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_map($upper, $headers), null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = sprintf('Convenios_%s.xlsx', date('dmY_his'));

        $response = new StreamedResponse(static fn () => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName),
        );

        return $response;
    }

    #[Route('/api/admin/conveniosadmin/convenios-generales-by-institucion/{institucion}', name: 'api.admin.conveniosadmin.convenios-generales-by-institucion', requirements: ['institucion' => '\d+'], methods: ['GET'])]
    public function getConveniosGeneralesByInstitucion(int $institucion, ConvenioRepository $convenioRepository): JsonResponse
    {
        $agreements = $convenioRepository->getConveniosGeneralesVigentesByInstitucion($institucion);

        return new JsonResponse([
            'data' => $this->normalizer->normalize($agreements, 'json', [
                'attributes' => ['id', 'nombre', 'fuica', 'fechaVenceConvenioFormatted', 'numero'],
            ]),
        ]);
    }

    #[Route('/api/admin/conveniosadmin', name: 'api.admin.convenios.store', methods: ['POST'])]
    public function store(
        Request $request,
        ConvenioRepositoryInterface $convenioRepositoryContract,
        ConvenioRepository $convenioRepository,
    ): JsonResponse {
        $form = $this->createForm(ConvenioAdminType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        try {
            /** @var Convenio $agreement */
            $agreement = $form->getData();
            $params = $request->request->all('appbundle_convenio_admin');

            $agreement->setVigencia($agreement->getFechaVenceConvenio());
            $agreement->setIsCamex(false);
            $this->applyFlags($agreement, $params);

            $this->em->persist($agreement);
            $this->em->flush();

            $number = $convenioRepositoryContract->nextNumber();
            $agreement->setFuica($convenioRepository->getCalculatedFuica(
                $agreement->getRfc(),
                $agreement->getTipo(),
                $agreement->getFechaFirma(),
                $agreement->getVigenciaText(),
                $number,
            ));
            $agreement->setNumero($number);
            $this->em->flush();

            $this->attachRelations($agreement, $params);
            $this->em->flush();

            $connection->commit();
        } catch (\Exception $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
                'exception' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->dispatcher->dispatch(new ConvenioEvent($agreement), ConvenioEvent::CONVENIO_CREADO);

        return new JsonResponse([
            'status' => true,
            'message' => 'Convenio creado con éxito',
        ]);
    }

    #[Route('/api/admin/conveniosadmin/{id}', name: 'api.admin.convenios.update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(Request $request, Convenio $convenio): JsonResponse
    {
        $form = $this->createForm(ConvenioAdminType::class, $convenio);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        $params = $request->request->all('appbundle_convenio_admin');

        $convenio->setVigencia($convenio->getFechaVenceConvenio());

        foreach ($convenio->getDelegacionConvenios() as $agreementDelegation) {
            $this->em->remove($agreementDelegation);
        }
        foreach ($convenio->getConveniosCiclosAcademicos() as $agreementCycle) {
            $this->em->remove($agreementCycle);
        }

        $this->attachRelations($convenio, $params);
        $this->applyFlags($convenio, $params);
        $this->em->flush();

        $this->dispatcher->dispatch(new ConvenioEvent($convenio), ConvenioEvent::CONVENIO_ACTUALIZADO);

        return new JsonResponse([
            'status' => true,
            'message' => 'Convenio actualizado con éxito',
        ]);
    }

    #[Route('/api/admin/conveniosadmin/fix/fuica', name: 'api.admin.convenios.fix.fuica', methods: ['POST'])]
    public function fuica(ConvenioRepository $convenioRepository): JsonResponse
    {
        foreach ($convenioRepository->findAll() as $agreement) {
            $agreement->setFuica($convenioRepository->getCalculatedFuica(
                $agreement->getRfc(),
                $agreement->getTipo(),
                $agreement->getFechaFirma(),
                $agreement->getVigenciaText(),
                $agreement->getNumero(),
            ));
        }
        $this->em->flush();

        return new JsonResponse(['status' => true]);
    }

    #[Route('/api/admin/conveniosadmin/fix/numeracion', name: 'api.admin.convenios.fix.numeracion', methods: ['POST'])]
    public function fixNumeracion(ConvenioRepository $convenioRepository): JsonResponse
    {
        $number = 1;
        foreach ($convenioRepository->findBy([], ['id' => 'ASC']) as $agreement) {
            $agreement->setNumero($number++);
        }
        $this->em->flush();

        return new JsonResponse(['status' => true]);
    }

    #[Route('/api/admin/conveniosadmin/{id}/status', name: 'api.admin.convenios.update-status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function changeStatus(Request $request, Convenio $convenio): JsonResponse
    {
        match ($request->request->get('status', 'active')) {
            'canceled' => $convenio->setCancelacionAnticipada(true),
            'active' => $convenio->setCancelacionAnticipada(false),
            default => null,
        };

        $this->em->flush();

        return new JsonResponse(['status' => true, 'message' => 'Convenio actualizado con éxito']);
    }

    private function applyFlags(Convenio $agreement, array $params): void
    {
        $agreement->setCartaIntencion((bool) ($params['cartaIntencion'] ?? false));
        $agreement->setTodasDelegaciones((bool) ($params['todasDelegaciones'] ?? false));
        $agreement->setTodosCiclosAcademicos((bool) ($params['todosCiclosAcademicos'] ?? false));
    }

    private function attachRelations(Convenio $agreement, array $params): void
    {
        $delegationRepository = $this->em->getRepository(Delegacion::class);
        $academicCycleRepository = $this->em->getRepository(CicloAcademico::class);

        foreach ($params['delegaciones'] ?? [] as $delegationId) {
            $agreementDelegation = new ConvenioDelegacion();
            $agreementDelegation->setConvenio($agreement);
            $agreementDelegation->setDelegacion($delegationRepository->find($delegationId));
            $this->em->persist($agreementDelegation);
        }

        foreach ($params['ciclosAcademicos'] ?? [] as $academicCycleId) {
            $agreementCycle = new ConvenioCicloAcademico();
            $agreementCycle->setConvenio($agreement);
            $agreementCycle->setCicloAcademico($academicCycleRepository->find($academicCycleId));
            $this->em->persist($agreementCycle);
        }
    }

    private function legacyPaginationParams(int $page, int $totalPages, int $totalRecords): array
    {
        return [
            'totalPaginas' => $totalPages,
            'totalRegistros' => $totalRecords,
            'anterior' => $page - 1,
            'siguiente' => $page + 1,
            'page' => $page,
            'isTheLastPage' => $page === $totalPages,
            'isTheLFirstPage' => $page === 1,
            'firstPage' => max($page - self::LEGACY_JUMP_LIMIT, 1),
            'lastPage' => min($page + self::LEGACY_JUMP_LIMIT, $totalPages),
            'minLimit' => ($page * self::LEGACY_PAGE_LIMIT) - self::LEGACY_PAGE_LIMIT + 1,
            'maxLimit' => $page === $totalPages ? $totalRecords : $page * self::LEGACY_PAGE_LIMIT,
        ];
    }
}
