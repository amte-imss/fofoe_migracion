<?php

namespace App\Controller\Admin\ConveniosEspeciales;

use App\Controller\DIEControllerController;
use App\Entity\ConvenioEspecial;
use App\Entity\ConvenioEspecialDelegacion;
use App\Entity\Delegacion;
use App\Entity\MotivoColaboracion;
use App\Entity\NivelAcademicoEspecial;
use App\Entity\Poblacion;
use App\Entity\Recurso;
use App\Form\Type\Institucion\Type\Convenios\ConvenioEspecialType;
use App\Normalizer\ConvenioEspecialNormalizer;
use App\Normalizer\UsuarioNormalizer;
use App\Repository\ConvenioEspecialRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
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

#[Route('/admin/convenios-especiales')]
class ConvenioEspecialController extends DIEControllerController
{
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/', name: 'admin.convenios-especiales.index', methods: ['GET', 'POST'])]
    public function index(Request $request, ConvenioEspecialRepository $convenioEspecialRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $agreementNormalizer = new ConvenioEspecialNormalizer();

            $filters = [
                'search' => (string) $this->input($request, 'search', ''),
                'type' => (string) $this->input($request, 'type', 'all'),
            ];

            $pagination = $convenioEspecialRepository->paginateAdminConvenios(
                $filters,
                (int) $this->input($request, 'page', 1),
                (int) $this->input($request, 'perPage', 20),
            );
            $pagination['data'] = $agreementNormalizer->serializarData($pagination['data']);

            return new JsonResponse($pagination);
        }

        return $this->render('admin/convenio-especial/index.html.twig', [
            'usuario' => $this->getUser(),
            'is_convenios_observer' => $this->isGranted('ROLE_CONVENIOS_OBSERVER'),
        ]);
    }

    #[Route('/new', name: 'admin.convenios-especiales.new', methods: ['GET'])]
    public function new(): Response
    {
        $userNormalizer = new UsuarioNormalizer();

        return $this->render('admin/convenio-especial/new.html.twig', [
            ...$this->catalogOptions(),
            'usuario' => $this->getUser(),
            'usuarioJson' => $userNormalizer->serialize($this->getUser()),
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('/store', name: 'api.admin.convenios-especiales.store', methods: ['POST'])]
    public function store(Request $request, ConvenioEspecialRepository $convenioEspecialRepository): JsonResponse
    {
        $form = $this->createForm(ConvenioEspecialType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var ConvenioEspecial $agreement */
        $agreement = $form->getData();
        $params = $request->request->all('appbundle_convenio_admin');

        $agreement->setVigencia($agreement->getFechaVenceConvenio());
        $agreement->setIsCamex(false);
        $agreement->setTodasDelegaciones((bool) ($params['todasDelegaciones'] ?? false));

        $this->em->persist($agreement);
        $this->em->flush();

        $number = $convenioEspecialRepository->nextNumber();
        $agreement->setFuica($convenioEspecialRepository->getCalculatedFuica(
            $agreement->getRfc(),
            $agreement->getTipo(),
            $agreement->getFechaFirma(),
            $agreement->getVigenciaText(),
            $number,
        ));
        $agreement->setNumero($number);

        $this->attachDelegations($agreement, $params);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Convenio creado con éxito',
        ]);
    }

    #[Route('/show', name: 'admin.convenios-especiales.show', methods: ['GET'])]
    public function show(Request $request, ConvenioEspecialRepository $convenioEspecialRepository): Response
    {
        return $this->render('admin/convenio-especial/show.html.twig', [
            'convenio' => $convenioEspecialRepository->find($request->query->get('id')),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin.convenios-especiales.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, ConvenioEspecialRepository $convenioEspecialRepository): JsonResponse
    {
        $errorResponse = new JsonResponse([
            'status' => false,
            'message' => 'No ha sido posible borrar el convenio, por favor intenta de nuevo.',
        ], Response::HTTP_BAD_REQUEST);

        $agreement = $convenioEspecialRepository->find($id);
        if ($agreement === null) {
            return $errorResponse;
        }

        try {
            $this->em->remove($agreement);
            $this->em->flush();
        } catch (\Exception) {
            return $errorResponse;
        }

        return new JsonResponse([
            'status' => true,
            'message' => 'El convenio ha sido borrado de forma exitosa.',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin.convenios-especiales.edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(ConvenioEspecial $convenioEspecial): Response
    {
        $userNormalizer = new UsuarioNormalizer();
        $agreementNormalizer = new ConvenioEspecialNormalizer();

        $collaborationReason = $convenioEspecial->getMotivoColaboracion();
        $resources = $collaborationReason === null
            ? []
            : $this->em->getRepository(Recurso::class)->findBy(['motivoColaboracionId' => $collaborationReason->getId()]);

        return $this->render('admin/convenio-especial/edit.html.twig', [
            ...$this->catalogOptions(),
            'usuario' => $this->getUser(),
            'usuarioJson' => $userNormalizer->serialize($this->getUser()),
            'convenio' => $agreementNormalizer->serializer($convenioEspecial),
            'recursos' => $this->normalizeIdName($resources),
            'maxDateAllowed' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    #[Route('/{id}/update', name: 'api.admin.convenios-especiales.update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(Request $request, ConvenioEspecial $convenio): JsonResponse
    {
        $form = $this->createForm(ConvenioEspecialType::class, $convenio);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        $params = $request->request->all('appbundle_convenio_admin');

        foreach ($convenio->getDelegacionConvenios() as $agreementDelegation) {
            $this->em->remove($agreementDelegation);
        }

        $this->attachDelegations($convenio, $params);
        $convenio->setTodasDelegaciones((bool) ($params['todasDelegaciones'] ?? false));
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Convenio actualizado con éxito',
        ]);
    }

    #[Route('/exportar', name: 'admin.convenios-especiales.exportar', methods: ['GET', 'POST'])]
    public function exportarConvenios(Request $request, ConvenioEspecialRepository $convenioEspecialRepository): Response
    {
        $filters = [
            'search' => (string) $this->input($request, 'search', ''),
            'type' => (string) $this->input($request, 'type', 'all'),
        ];

        $agreements = $convenioEspecialRepository->getAdminConvenios($filters)
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();

        if (empty($agreements)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $headers = [
            'Numero de Convenio', 'R.F.C. de Convenio', 'Razon Social', 'Nombre Comercial', 'Institucion', 'OOAD',
            'Sector', 'Tipo', 'Objetivo de la Colaboración', 'Motivo de la Colaboración', 'Recursos',
            'Poblacion objetivo o blanco', 'Nivel o grado academico', 'Fecha firma de convenio',
            'Vigencia de convenio', 'Vencimiento del convenio', 'Cargo firmante IMSS 1ro.',
            'Nombre firmante IMSS 1ro.', 'Cargo institucion 1ro.', 'Nombre institucion 1ro.',
            'Cargo firmante IMSS 2do.', 'Nombre firmante IMSS 2do.', 'Cargo institucion 2do.',
            'Nombre institucion 2do.', 'Fuica', 'URL de documento',
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
        /** @var ConvenioEspecial $agreement */
        foreach ($agreements as $agreement) {
            $rows[] = [
                $upper($agreement->getNumero()),
                $upper($agreement->getRfc()),
                $upper($agreement->getRazonSocial()),
                $upper($agreement->getNombre()),
                $upper($agreement->getInstitucion()?->getNombre()),
                $upper($agreement->getConvenioDelegacionesFormatted()),
                $upper($agreement->getSector()),
                $upper($agreement->getTipo()),
                $upper($agreement->getObjetivoColaboracion()),
                $upper($agreement->getMotivoColaboracion()?->getNombre()),
                $upper($agreement->getRecurso()?->getNombre()),
                $upper($agreement->getPoblacion()?->getNombre()),
                $upper($agreement->getNivelAcademico()?->getNombre()),
                $upper($agreement->getFechaFirmaFormatted()),
                $upper($agreement->getVigenciaTextFormatted()),
                $upper($agreement->getFechaVenceConvenioFormatted()),
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
            ];
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($this->getUser()?->getUserIdentifier() ?? '')
            ->setTitle('Convenios Especiales')
            ->setSubject('Lista de Exportación de Convenios Especiales')
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

    /**
     * Replacement for the removed Request::get(): query string first, then POST body.
     */
    private function input(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->query->has($key)
            ? $request->query->get($key)
            : $request->request->get($key, $default);
    }

    private function attachDelegations(ConvenioEspecial $agreement, array $params): void
    {
        $delegationRepository = $this->em->getRepository(Delegacion::class);

        foreach ($params['delegaciones'] ?? [] as $delegationId) {
            $agreementDelegation = new ConvenioEspecialDelegacion();
            $agreementDelegation->setConvenio($agreement);
            $agreementDelegation->setDelegacion($delegationRepository->find($delegationId));
            $this->em->persist($agreementDelegation);
        }
    }

    private function catalogOptions(): array
    {
        return [
            'nivelesAcademicos' => $this->normalizeIdName($this->em->getRepository(NivelAcademicoEspecial::class)->findAll()),
            'motivosColaboracion' => $this->normalizeIdName($this->em->getRepository(MotivoColaboracion::class)->findAll()),
            'poblaciones' => $this->normalizeIdName($this->em->getRepository(Poblacion::class)->findAll()),
        ];
    }

    private function normalizeIdName(array $entities): array
    {
        return $this->normalizer->normalize($entities, 'json', ['attributes' => ['id', 'nombre']]);
    }
}
