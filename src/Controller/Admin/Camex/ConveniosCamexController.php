<?php

namespace App\Controller\Admin\Camex;

use App\Controller\DIEControllerController;
use App\Entity\Carrera;
use App\Entity\CicloAcademico;
use App\Entity\Convenio;
use App\Entity\ConvenioCicloAcademico;
use App\Entity\ConvenioDelegacion;
use App\Entity\ConvenioEliminado;
use App\Entity\Delegacion;
use App\Entity\Institucion as EntityInstitucion;
use App\Event\ConvenioEvent;
use App\Form\ConvenioAdminType;
use App\Normalizer\ConvenioNormalizer;
use App\Normalizer\UsuarioNormalizer;
use App\Repository\ConvenioRepositoryInterface;
use App\Repository\DisciplinaRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConveniosCamexController extends DIEControllerController
{

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly PaginatorInterface $objPaginador,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/admin/convenioscame/convenioscame', name: 'admin.convenioscame.convenioscame.index', methods: ['POST', 'GET'])]
    public function index(Request $request, ConvenioRepositoryInterface $convenioRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $serializer = new ConvenioNormalizer();
            $page       = $request->get('page', 1);
            $perPage    = $request->get('perPage', 20);
            $search     = $request->get('search', '');

            /** @var \App\Entity\Usuario $user */
            $user = $this->getUser();

            if ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS')) {
                $delegacionesIds = $this->getOOADByUMAE(
                    $this->getUserUnidad()->getDelegacion()->getId()
                );
            } else {
                $delegacionesIds = $user->getDelegaciones()->map(
                    fn($item) => $item->getId()
                );
            }

            $filters    = ['search' => $search, 'type' => 'active', 'came_delegacion' => $delegacionesIds];
            $pagination = $convenioRepository->paginateAdminConvenios($filters, $page, $perPage);
            $pagination['data'] = $serializer->serializarData($pagination['data']);

            return new JsonResponse($pagination);
        }

        $normalizer  = new UsuarioNormalizer();
        $usuarioJson = $normalizer->serialize($this->getUser());

        return $this->render('admin/conveniocamex/index.html.twig', [
            'usuario'     => $this->getUser(),
            'usuarioJson' => $usuarioJson,
        ]);
    }

    public function getOOADByUMAE(int $ooad_umae): array
    {
        if (in_array($ooad_umae, [35, 36])) return [35, 36];
        if (in_array($ooad_umae, [37, 38])) return [37, 38];
        return [$ooad_umae];
    }

    #[Route('/admin/convenioscame/convenioscame/{id}/deleted', name: 'admin.convenioscame.convenioscame.delete', methods: ['POST'])]
    public function convenioCameBorrado(
        Request $request,
        EventDispatcherInterface $dispatcher,
        int $id
    ): JsonResponse {
        $convenioRepo = $this->em->getRepository(Convenio::class);
        $convenio     = $convenioRepo->find($id);

        if ($convenio) {
            $dispatcher->dispatch(new ConvenioEvent($convenio), ConvenioEvent::CONVENIO_ELIMINADO);

            $registro = new ConvenioEliminado();
            $registro->setConvenioInfo(json_encode($convenio->toArray(), JSON_UNESCAPED_UNICODE));
            $registro->setUsuario($this->getUser());
            $registro->setFuica($convenio->getFuica());
            $registro->setNumero($convenio->getNumero());
            $registro->setRfc($convenio->getRfc());
            $this->em->persist($registro);
            $this->em->remove($convenio);
            $this->em->flush();
        }

        // deleteConvenioCame ya no tiene sentido si remove+flush ya lo borró.
        // Evalúa si este método legacy aún se necesita.
        $convenioBorrado = $convenioRepo->deleteConvenioCame($id);

        if (!empty($convenioBorrado)) {
            return new JsonResponse(['status' => false, 'message' => 'No ha sido posible borrar el convenio, por favor intenta de nuevo.'], 400);
        }

        return new JsonResponse(['status' => true, 'message' => 'El convenio ha sido borrado de forma exitosa.']);
    }

    #[Route('/admin/convenioscame/convenioscame/new', name: 'admin.convenioscame.convenioscame.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $normalizer  = new UsuarioNormalizer();
        $usuarioJson = $normalizer->serialize($this->getUser());

        return $this->render('admin/conveniocamex/new.html.twig', [
            'usuario'        => $this->getUser(),
            'usuarioJson'    => $usuarioJson,
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('api/came/conveniosadmin', name: 'api.came.convenios.store', methods: ['POST'])]
    public function store(
        Request $request,
        ConvenioRepositoryInterface $convenioRepository,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $repository        = $this->em->getRepository(Convenio::class);
        $delegacionRepo    = $this->em->getRepository(Delegacion::class);
        $cicloAcademicoRepo = $this->em->getRepository(CicloAcademico::class);

        $formulario = $this->createForm(ConvenioAdminType::class);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $this->em->getConnection()->beginTransaction();
            try {
                /** @var \App\Entity\Convenio $data */
                $data   = $formulario->getData();
                $params = $request->get('appbundle_convenio_admin');

                $data->setVigencia($data->getFechaVenceConvenio());
                $data->setCartaIntencion(boolval($params['cartaIntencion']));
                $data->setTodasDelegaciones(boolval($params['todasDelegaciones']));
                $data->setTodosCiclosAcademicos(boolval($params['todosCiclosAcademicos']));

                if ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS')) {
                    $data->setIsUmae(true);
                } else {
                    $data->setIsCamex(true);
                }

                $this->em->persist($data);
                $this->em->flush();

                $numero = $convenioRepository->nextNumber();
                $fuica  = $repository->getCalculatedFuica(
                    $data->getRfc(), $data->getTipo(),
                    $data->getFechaFirma(), $data->getVigenciaText(), $numero
                );
                $data->setFuica($fuica);
                $data->setNumero($numero);
                $this->em->persist($data);
                $this->em->flush();

                if (isset($params['delegaciones'])) {
                    foreach ($params['delegaciones'] as $delegacionId) {
                        $dc = new ConvenioDelegacion();
                        $dc->setConvenio($data);
                        $dc->setDelegacion($delegacionRepo->find($delegacionId));
                        $this->em->persist($dc);
                    }
                    $data->setDelegacion($delegacionRepo->find($params['delegaciones'][0]));
                }

                if (isset($params['ciclosAcademicos'])) {
                    foreach ($params['ciclosAcademicos'] as $cicloId) {
                        $cca = new ConvenioCicloAcademico();
                        $cca->setConvenio($data);
                        $cca->setCicloAcademico($cicloAcademicoRepo->find($cicloId));
                        $this->em->persist($cca);
                    }
                }

                $this->em->persist($data);
                $this->em->flush();
                $this->em->getConnection()->commit();

                $dispatcher->dispatch(new ConvenioEvent($data), ConvenioEvent::CONVENIO_CREADO);

                return new JsonResponse(['status' => true, 'message' => 'Convenio creado con éxito']);
            } catch (\Exception $ex) {
                $this->em->getConnection()->rollBack();
                return new JsonResponse([
                    'status'    => false,
                    'message'   => 'Error',
                    'errors'    => $this->getFormErrors($formulario, true),
                    'exception' => $ex->getMessage(),
                ], 400);
            }
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($formulario, true),
        ], 400);
    }

    #[Route('/admin/convenioscame/convenioscame/edit/{id}', name: 'admin.convenioscame.convenioscame.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Convenio $convenio): Response
    {
        $serializer  = new ConvenioNormalizer();
        $normalizer  = new UsuarioNormalizer();

        return $this->render('admin/conveniocamex/edit.html.twig', [
            'usuario'        => $this->getUser(),
            'convenio'       => $serializer->serializer($convenio),
            'usuarioJson'    => $normalizer->serialize($this->getUser()),
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('api/came/conveniosadmin/{id}', name: 'api.came.convenios.update', methods: ['POST'])]
    public function update(
        Request $request,
        EventDispatcherInterface $dispatcher,
        Convenio $convenio
    ): JsonResponse {
        $delegacionRepo     = $this->em->getRepository(Delegacion::class);
        $cicloAcademicoRepo = $this->em->getRepository(CicloAcademico::class);

        $formulario = $this->createForm(ConvenioAdminType::class, $convenio);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            /** @var \App\Entity\Convenio $data */
            $data   = $formulario->getData();
            $params = $request->get('appbundle_convenio_admin');

            $data->setVigencia($data->getFechaVenceConvenio());

            foreach ($convenio->getDelegacionConvenios() as $dc) {
                $this->em->remove($dc);
            }
            foreach ($convenio->getConveniosCiclosAcademicos() as $cca) {
                $this->em->remove($cca);
            }

            if (isset($params['delegaciones'])) {
                foreach ($params['delegaciones'] as $delegacionId) {
                    $dc = new ConvenioDelegacion();
                    $dc->setConvenio($convenio);
                    $dc->setDelegacion($delegacionRepo->find($delegacionId));
                    $this->em->persist($dc);
                }
                $data->setDelegacion($delegacionRepo->find($params['delegaciones'][0]));
            }

            if (isset($params['ciclosAcademicos'])) {
                foreach ($params['ciclosAcademicos'] as $cicloId) {
                    $cca = new ConvenioCicloAcademico();
                    $cca->setConvenio($convenio);
                    $cca->setCicloAcademico($cicloAcademicoRepo->find($cicloId));
                    $this->em->persist($cca);
                }
            }

            $data->setCartaIntencion(boolval($params['cartaIntencion']));
            $data->setTodasDelegaciones(boolval($params['todasDelegaciones']));
            $data->setTodosCiclosAcademicos(boolval($params['todosCiclosAcademicos']));

            $this->em->persist($data);
            $this->em->flush();

            $dispatcher->dispatch(new ConvenioEvent($convenio), ConvenioEvent::CONVENIO_ACTUALIZADO);

            return new JsonResponse(['status' => true, 'message' => 'Convenio actualizado con éxito']);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($formulario, true),
        ], 400);
    }

    #[Route('/admin/convenioscame/convenioscame/show', name: 'admin.convenioscame.convenioscame.show', methods: ['GET', 'POST'])]
    public function show(Request $request): Response
    {
        $convenio = $this->em->getRepository(Convenio::class)->find($request->get('id'));

        return $this->render('admin/conveniocamex/show.html.twig', [
            'convenio' => $convenio,
            'usuario'  => $this->getUser(),
        ]);
    }

    #[Route('/admin/convenioscame/getconveniogral', name: 'admin.convenioscame.getconveniogral', methods: ['POST', 'GET'])]
    public function getConveniosGenerales(Request $request): Response
    {
        $conveniosColl = ['status' => 'no', 'data' => null];

        $institucionRepo = $this->em->getRepository(EntityInstitucion::class);
        $objsInstituciones = $institucionRepo->getInstitucionPorRFC($request->get('rfc'));

        $convenioRepo = $this->em->getRepository(Convenio::class);
        $conveniosColl['data'] = $convenioRepo->getConveniosPorRfcDeInstitucion($objsInstituciones);

        if (!empty($conveniosColl['data'])) {
            $conveniosColl['status']       = 'si';
            $conveniosColl['instituciones'] = $convenioRepo->getListaInstituciones($objsInstituciones);
            $conveniosColl['razonSocial']   = $convenioRepo->getRazonSocialInstitucion($objsInstituciones);
        }

        return new Response(json_encode($conveniosColl));
    }

    #[Route('/admin/convenioscame/getcarrera', name: 'admin.convenioscame.getcarrera', methods: ['POST', 'GET'])]
    public function getCarrera(Request $request): Response
    {
        $esOtra  = ['status' => 'no'];
        $carrera = $this->em->getRepository(Carrera::class)->find($request->get('selectedId'));

        if (str_contains(strtolower($carrera->getNombre()), 'otra')) {
            $esOtra = ['status' => 'si'];
        }

        return new Response(json_encode($esOtra));
    }

    #[Route('/admin/convenioscame/getnivelacademico', name: 'admin.convenioscame.getnivelacademico', methods: ['POST', 'GET'])]
    public function getNivelAcademico(Request $request): Response
    {
        $result = ['status' => 'no', 'data' => null];
        $nivel  = $this->em->getRepository(\App\Entity\NivelAcademico::class)->find($request->get('selectedId'));

        if (!empty($nivel)) {
            $result['status'] = 'si';
            $result['data']   = str_contains(strtolower($nivel->getNombre()), 'otro') ? 'true' : null;
        }

        return new Response(json_encode($result));
    }

    #[Route('/admin/convenioscame/getdisciplina', name: 'admin.convenioscame.getdisciplina', methods: ['POST', 'GET'])]
    public function getDisciplina(Request $request): Response
    {
        $result     = ['status' => 'no', 'data' => null];
        $disciplina = $this->em->getRepository(\App\Entity\Disciplina::class)->find($request->get('selectedId'));

        if (!empty($disciplina)) {
            $result['status'] = 'si';
            $result['data']   = str_contains(strtolower($disciplina->getNombre()), 'otra') ? 'true' : null;
        }

        return new Response(json_encode($result));
    }

    #[Route('/admin/convenioscame/convenioscame/exportar', name: 'admin.convenioscame.convenioscame.exportar', methods: ['GET', 'POST'])]
    public function exportarConvenios(Request $request): Response
    {
        /** @var \App\Entity\Usuario $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS')) {
            $delegacionesIds = $this->getOOADByUMAE($this->getUserUnidad()->getDelegacion()->getId());
        } else {
            $delegacionesIds = $user->getDelegaciones()->map(fn($item) => $item->getId());
        }

        $conveniosList = $this->em->getRepository(Convenio::class)
            ->getAdminConvenios(['came_delegacion' => $delegacionesIds, 'type' => 'active'])
            ->getQuery()
            ->getResult();

        if (empty($conveniosList)) {
            return new Response('', 204);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Convenios CAME')
            ->setSubject('Lista de Exportación de Convenios');

        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'NUMERO DE CONVENIO', 'B' => 'R.F.C. DE CONVENIO', 'C' => 'RAZON SOCIAL',
            'D' => 'NOMBRE COMERCIAL', 'E' => 'INSTITUCION', 'F' => 'CONVENIO GENERAL',
            'G' => 'OOAD', 'H' => 'SECTOR', 'I' => 'TIPO', 'J' => 'OBJETIVO DE LA COLABORACION',
            'K' => 'CICLOS ACADÉMICOS', 'L' => 'NIVEL O GRADO ACADEMICO', 'M' => 'DISCIPLINA',
            'N' => 'CARRERA', 'O' => 'NOMBRE DEL PROGRAMA', 'P' => 'FECHA FIRMA DE CONVENIO',
            'Q' => 'VIGENCIA DE CONVENIO', 'R' => 'VENCIMIENTO DEL CONVENIO', 'S' => 'EMISIÓN OTA',
            'T' => 'VENCIMIENTO OTA', 'U' => 'EMISIÓN RVOE', 'V' => 'VENCIMIENTO RVOE',
            'W' => 'EMISIÓN COMAEM', 'X' => 'VENCIMIENTO COMAEM',
            'Y' => 'CARGO FIRMANTE IMSS 1RO.', 'Z' => 'NOMBRE FIRMANTE IMSS 1RO.',
            'AA' => 'CARGO INSTITUCION 1RO.', 'AB' => 'NOMBRE INSTITUCION 1RO.',
            'AC' => 'CARGO FIRMANTE IMSS 2DO.', 'AD' => 'NOMBRE FIRMANTE IMSS 2DO.',
            'AE' => 'CARGO INSTITUCION 2DO.', 'AF' => 'NOMBRE INSTITUCION 2DO.',
            'AG' => 'FUICA', 'AH' => 'URL DE DOCUMENTO',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
        }

        $row = 2;
        foreach ($conveniosList as $objConvenio) {
            /** @var Convenio $objConvenio */
            $sheet->setCellValue('A' . $row, mb_strtoupper($objConvenio->getNumero()));
            $sheet->setCellValue('B' . $row, mb_strtoupper($objConvenio->getRfc()));
            $sheet->setCellValue('C' . $row, mb_strtoupper($objConvenio->getRazonSocial()));
            $sheet->setCellValue('D' . $row, mb_strtoupper($objConvenio->getNombre()));
            $sheet->setCellValue('E' . $row, mb_strtoupper($objConvenio->getInstitucion()->getNombre()));
            $sheet->setCellValue('F' . $row, $objConvenio->getConvenioGeneral() ? mb_strtoupper($objConvenio->getConvenioGeneral()->getNombre()) : '');
            $sheet->setCellValue('G' . $row, mb_strtoupper($objConvenio->getConvenioDelegacionesFormatted()));
            $sheet->setCellValue('H' . $row, mb_strtoupper($objConvenio->getSector()));
            $sheet->setCellValue('I' . $row, mb_strtoupper($objConvenio->getTipo()));
            $sheet->setCellValue('J' . $row, mb_strtoupper($objConvenio->getObjetivoColaboracion()));
            $sheet->setCellValue('K' . $row, mb_strtoupper($objConvenio->getCiclosAcademicosFormatted()));
            $sheet->setCellValue('L' . $row, $objConvenio->getNivelAcademico() ? mb_strtoupper($objConvenio->getNivelAcademico()->getNombre()) : '');
            $sheet->setCellValue('M' . $row, $objConvenio->getDisciplina() ? mb_strtoupper($objConvenio->getDisciplina()->getNombre()) : '');
            $sheet->setCellValue('N' . $row, $objConvenio->getCarrera() ? mb_strtoupper($objConvenio->getCarrera()->getNombre()) : '');
            $sheet->setCellValue('O' . $row, mb_strtoupper($objConvenio->getNombrePrograma()));
            $sheet->setCellValue('P' . $row, mb_strtoupper($objConvenio->getFechaFirmaFormatted()));
            $sheet->setCellValue('Q' . $row, mb_strtoupper($objConvenio->getVigenciaTextFormatted()));
            $sheet->setCellValue('R' . $row, mb_strtoupper($objConvenio->getFechaVenceConvenioFormatted()));
            $sheet->setCellValue('S' . $row, mb_strtoupper($objConvenio->getFechaOtaFormatted()));
            $sheet->setCellValue('T' . $row, mb_strtoupper($objConvenio->getFechaVenceOtaFormatted()));
            $sheet->setCellValue('U' . $row, mb_strtoupper($objConvenio->getFechaRvoeFormatted()));
            $sheet->setCellValue('V' . $row, mb_strtoupper($objConvenio->getFechaVenceRvoeFormatted()));
            $sheet->setCellValue('W' . $row, mb_strtoupper($objConvenio->getFechaEmiteComaemFormatted()));
            $sheet->setCellValue('X' . $row, mb_strtoupper($objConvenio->getFechaVenceComaemFormatted()));

            try { $sheet->setCellValue('Y' . $row, $objConvenio->getCargoFirmaIdFirst() ? mb_strtoupper($objConvenio->getCargoFirmaIdFirst()->getNombre()) : ''); }
            catch (\Exception) { $sheet->setCellValue('Y' . $row, ''); }

            $sheet->setCellValue('Z' . $row,  mb_strtoupper($objConvenio->getRecibeSignfirst()));
            $sheet->setCellValue('AA' . $row, mb_strtoupper($objConvenio->getCargoEmiteSignfirst()));
            $sheet->setCellValue('AB' . $row, mb_strtoupper($objConvenio->getNombreEmiteSignfirst()));

            try { $sheet->setCellValue('AC' . $row, $objConvenio->getCargoFirmaIdSecond() ? mb_strtoupper($objConvenio->getCargoFirmaIdSecond()->getNombre()) : ''); }
            catch (\Exception) { $sheet->setCellValue('AC' . $row, ''); }

            $sheet->setCellValue('AD' . $row, mb_strtoupper($objConvenio->getRecibeSignsecond()));
            $sheet->setCellValue('AE' . $row, mb_strtoupper($objConvenio->getCargoEmiteSignsecond()));
            $sheet->setCellValue('AF' . $row, mb_strtoupper($objConvenio->getNombreEmiteSignsecond()));
            $sheet->setCellValue('AG' . $row, mb_strtoupper($objConvenio->getFuica()));
            $sheet->setCellValue('AH' . $row, mb_strtoupper($objConvenio->getUrlConvenio()));

            $row++;
        }

        $fileName = 'ConveniosCame_' . date('dmY_His') . '.xlsx';
        $tmpPath  = '/tmp/' . $fileName;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpPath);

        return new Response(file_get_contents($tmpPath), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function getConveniosGeneralesByInstitucion(
        Request $request,
        int $institucion,
        NormalizerInterface $serializer
    ): JsonResponse {
        $data = $this->em->getRepository(Convenio::class)
            ->getConveniosGeneralesVigentesByInstitucion($institucion);

        return new JsonResponse([
            'data' => $serializer->normalize($data, 'json', [
                'attributes' => ['id', 'nombre', 'fuica', 'fechaVenceConvenioFormatted', 'numero'],
            ]),
        ]);
    }
}
