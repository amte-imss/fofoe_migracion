<?php

namespace App\Controller\Admin;

use App\Controller\DIEControllerController;
use App\Entity\Institucion;
use App\Form\Type\Institucion\Type\Admin\InstitucionType;
use App\Repository\InstitucionRepositoryInterface;
use App\Service\Export\InstitucionExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Exception\NoFileFoundException;
use Vich\UploaderBundle\Handler\DownloadHandler;

class InstitucionController extends DIEControllerController
{
    private const array LIST_ATTRIBUTES = [
        'razonSocial', 'id', 'nombre', 'rfc', 'direccion', 'telefono', 'cedulaIdentificacion',
        'extension', 'correo', 'sitioWeb', 'fax', 'representante',
    ];

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        #[Autowire('%app.institution_uploads_dir%')]
        private readonly string $institutionUploadsDir,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/admin/institucion', name: 'admin.institucion.index', methods: ['GET'])]
    public function index(Request $request, InstitucionRepositoryInterface $institucionRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pagination = $institucionRepository->paginateAdminInstituciones(
                ['search' => (string) $request->query->get('search', '')],
                $request->query->getInt('page', 1),
                $request->query->getInt('perPage', 20),
            );
            $pagination['data'] = $this->normalizer->normalize($pagination['data'], 'json', [
                'attributes' => self::LIST_ATTRIBUTES,
            ]);

            return new JsonResponse($pagination);
        }

        return $this->render('admin/institucion/index.html.twig', [
            'usuario' => $this->getUser(),
            'is_convenios_observer' => $this->isGranted('ROLE_CONVENIOS_OBSERVER'),
        ]);
    }

    #[Route('/admin/institucion/new', name: 'admin.institucion.new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/institucion/new.html.twig', [
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/admin/institucion/edit/{id}', name: 'admin.institucion.edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(Institucion $institucion): Response
    {
        return $this->render('admin/institucion/edit.html.twig', [
            'usuario' => $this->getUser(),
            'institucion' => $this->normalizer->normalize($institucion, 'json', [
                'attributes' => [...self::LIST_ATTRIBUTES, 'delegacion' => ['id', 'nombre']],
            ]),
            'hasCedula' => $this->hasCedula($institucion) ? 1 : 0,
        ]);
    }

    #[Route('/admin/institucion/export', name: 'admin.institucion.conveniosadmin.export', methods: ['GET', 'POST'])]
    public function export(InstitucionExportService $exportService): Response
    {
        $fileName = sprintf('Instituciones_%s.xlsx', date('dmY_his'));

        return new Response($exportService->export(), Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName),
        ]);
    }

    #[Route('/admin/institucion/{id}', name: 'admin.institucion.show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(Institucion $institucion): Response
    {
        return $this->render('admin/institucion/show.html.twig', [
            'institucion' => $institucion,
            'usuario' => $this->getUser(),
            'hasCedula' => $this->hasCedula($institucion),
        ]);
    }

    #[Route('/api/admin/institucion', name: 'api.admin.institucion.store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(InstitucionType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Institucion $institution */
        $institution = $form->getData();

        if ($institution->getCedulaFile() !== null) {
            $institution->setCedulaIdentificacion($institution->getCedulaFile()->getClientOriginalName());
        }

        $this->em->persist($institution);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Institución creada con éxito',
        ]);
    }

    #[Route('/api/admin/institucion/{id}', name: 'api.admin.institucion.update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(Request $request, Institucion $institucion): JsonResponse
    {
        $oldCedula = $institucion->getCedulaIdentificacion();

        $form = $this->createForm(InstitucionType::class, $institucion);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error en la actualización',
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        $uploadedCedula = $institucion->getCedulaFile();
        $institucion->setCedulaIdentificacion($uploadedCedula?->getClientOriginalName() ?? $oldCedula);

        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Institución actualizada con éxito',
            'oldCedula' => $oldCedula,
        ]);
    }

    #[Route('/admin/institucion/{id}/deleted', name: 'admin.institucion.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function institucionBorrado(Institucion $institucion): JsonResponse
    {
        try {
            $this->em->remove($institucion);
            $this->em->flush();
        } catch (\Exception) {
            return new JsonResponse([
                'status' => false,
                'message' => 'No ha sido posible borrar la institución, por favor intenta de nuevo.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => true,
            'message' => 'La institución ha sido borrada de forma exitosa.',
        ]);
    }

    #[Route('/admin/institucion/{id}/cedula', name: 'name.institucion.cedula', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function downloadCedula(Request $request, Institucion $institucion, DownloadHandler $downloadHandler): Response
    {
        try {
            $response = $downloadHandler->downloadObject($institucion, 'cedulaFile');
        } catch (NoFileFoundException) {
            throw $this->createNotFoundException();
        }

        if ($request->query->has('inline')) {
            $response->headers->set(
                'Content-Disposition',
                HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'identificacion-fiscal.pdf'),
            );
            $response->headers->set('Content-Type', 'application/pdf');
        }

        return $response;
    }

    #[Route('/admin/institucion/search-by-rfc/{rfc}', name: 'admin.institucion.search.rfc', methods: ['GET'])]
    public function searchByRfc(string $rfc, InstitucionRepositoryInterface $institucionRepository): JsonResponse
    {
        $institutions = $institucionRepository->findBy(['rfc' => $rfc]);

        return new JsonResponse($this->normalizer->normalize($institutions, 'json', [
            'attributes' => ['razonSocial', 'id', 'nombre', 'rfc'],
        ]));
    }

    private function hasCedula(Institucion $institution): bool
    {
        return file_exists(sprintf('%s/%d/identificacion-fiscal.pdf', $this->institutionUploadsDir, $institution->getId()));
    }
}
