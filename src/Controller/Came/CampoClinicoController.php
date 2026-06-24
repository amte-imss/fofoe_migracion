<?php

namespace App\Controller\Came;

use App\Calculator\CampoClinicoCalculatorInterface;
use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use App\Entity\Unidad;
use App\Entity\Usuario;
use App\Form\Type\FormatoFofoeFileType;
use App\Form\Type\RegistraCampoClinico\CampoClinicoType;
use App\Repository\CampoClinicoRepositoryInterface;
use App\Service\CampoClinicoManagerInterface;
use App\Service\GeneradorCredencialesInterface;
use App\Service\GeneradorFormatoFofoeInterface;
use App\Controller\DIEControllerController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Handler\DownloadHandler;

class CampoClinicoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly DownloadHandler $downloadHandler,
        private readonly string $formatoFofoeDir,
        private readonly string $credencialesDir,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/came/api/campo_clinico', methods: ['POST'], name: 'came.campo_clinico.store')]
    public function store(Request $request, CampoClinicoManagerInterface $campoClinicoManager): Response
    {
        $solicitud_id = $request->request->get('campo_clinico')['solicitud'];
        $solicitud    = $this->em->getRepository(Solicitud::class)->find($solicitud_id);

        if (!$solicitud) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGrantedUserAccessToSolicitud($solicitud)) {
            return $this->httpErrorResponse();
        }
        if (!in_array($solicitud->getEstatus(), [Solicitud::CREADA], strict: true)) {
            return $this->httpErrorResponse('No puedes modificar la solicitud ' . $solicitud->getNoSolicitud());
        }

        $form = $this->createForm(CampoClinicoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->jsonResponse($campoClinicoManager->create($form->getData()));
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/came/api/campo_clinico/{campo_clinico_id}', methods: ['DELETE'], name: 'came.campo_clinico.delete', requirements: ['campo_clinico_id' => '\d+'])]
    public function delete(
        Request $request,
        CampoClinicoManagerInterface $campoClinicoManager,
        int $campo_clinico_id
    ): Response {
        /** @var CampoClinico $campoClinico */
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGrantedUserAccessToSolicitud($campoClinico->getSolicitud())) {
            return $this->httpErrorResponse();
        }
        if (!in_array($campoClinico->getSolicitud()->getEstatus(), [Solicitud::CREADA], strict: true)) {
            return $this->httpErrorResponse('Campo Clinico only can delete if solicitud.status is "CREADA"');
        }
        if ($campoClinico->getSolicitud()->getCampoClinicos()->count() <= 1) {
            return $this->httpErrorResponse('Must exist almost one campoclinico');
        }

        return $this->jsonResponse($campoClinicoManager->delete($campoClinico));
    }

    #[Route('/came/campo_clinico/create', methods: ['GET'], name: 'came.campo_clinico.create')]
    public function create(): Response
    {
        $form = $this->createForm(CampoClinicoType::class);
        return $this->render('came/campo_clinico/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/came/api/solicitud/{id}/campos_clinicos', methods: ['GET'], name: 'solicitud.index.campo_clinico.json')]
    public function indexApi(Request $request, int $id): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);

        if (!$solicitud) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGrantedUserAccessToSolicitud($solicitud)) {
            return $this->httpErrorResponse('No puedes ver una solicitud de otra delegación');
        }

        $perPage        = $request->query->get('perPage', 10);
        $page           = $request->query->get('page', 1);
        $camposClinicos = $this->em->getRepository(CampoClinico::class)
            ->getAllCamposClinicosBySolicitud($id, $perPage, $page, $request->query->all());

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($camposClinicos, 'json', [
                'attributes' => [
                    'id',
                    'trabajadoresBecados' => ['matricula'],
                    'formatoFofoeFileName',
                    'cicloAcademico'      => ['id', 'nombre'],
                    'convenio'            => [
                        'cicloAcademico' => ['id', 'nombre'],
                        'id', 'vigencia', 'label',
                        'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                        'numero',
                    ],
                    'lugaresSolicitados', 'lugaresAutorizados', 'horario',
                    'unidad'              => ['id', 'nombre', 'claveUnidad'],
                    'fechaInicial', 'fechaFinal', 'referenciaBancaria',
                    'fechaInicialFormatted', 'fechaFinalFormatted',
                    'estatus'             => ['id', 'nombre'],
                ],
            ]),
        ]);
    }

    #[Route('/formato/campo_clinico/{campo_clinico_id}/formato_fofoe/show', methods: ['GET'], name: 'campo_clinico.formato_fofoe.show')]
    public function showFormatoFofoe(int $campo_clinico_id, CampoClinicoCalculatorInterface $calculator): Response
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }

        $montoCC = $campoClinico->getMonto() > 0
            ? $campoClinico->getMonto()
            : $calculator->getMontoAPagar($campoClinico, $campoClinico->getSolicitud());

        return $this->render('formatos/fofoe.html.twig', [
            'campo_clinico' => $campoClinico,
            'came'          => $this->getCAMEorJDES($campoClinico),
            'montoCC'       => $montoCC,
        ]);
    }

    #[Route('/came/campo_clinico/{campo_clinico_id}/formato_fofoe_firmado/download', methods: ['GET'], name: 'campo_clinico.formato_fofoe_firmado.download', requirements: ['campo_clinico_id' => '\d+'])]
    public function downloadFormatoFofoeFirmado(int $campo_clinico_id): Response
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }
        if (!$this->isGrantedUserAccessToSolicitud($campoClinico->getSolicitud())) {
            $this->addFlash('danger', 'No tiene acceso a la información solicitada');
            return $this->redirectToRoute('came.solicitud.index');
        }

        return $this->downloadHandler->downloadObject($campoClinico, 'formatoFofoeFile');
    }

    #[Route('/formato/campo_clinico/{campo_clinico_id}/formato_fofoe/download', methods: ['GET'], name: 'campo_clinico.formato_fofoe.download', requirements: ['campo_clinico_id' => '\d+'])]
    public function downloadFormatoFofoe(
        Request $request,
        GeneradorFormatoFofoeInterface $generadorFormatoFofoe,
        int $campo_clinico_id
    ): Response {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }

        $overwrite       = $request->query->get('overwrite', false);
        $formatoFofoeFile = $generadorFormatoFofoe->responsePdf($this->formatoFofoeDir, $campoClinico, $overwrite);
        $fileContent     = file_get_contents($formatoFofoeFile);
        $filesize        = filesize($formatoFofoeFile);
        unlink($formatoFofoeFile);

        return new Response($fileContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment;filename="' . $generadorFormatoFofoe->getFileName($campoClinico) . '"',
            'Content-length'      => $filesize,
        ]);
    }

    #[Route('/came/api/campo_clinico/{campo_clinico_id}/formato_fofoe/upload', methods: ['POST'], name: 'campo_clinico.formato_fofoe.upload')]
    public function uploadFormatoFofoe(
        Request $request,
        CampoClinicoRepositoryInterface $campoClinicoRepository,
        CampoClinicoManagerInterface $campoClinicoManager,
        int $campo_clinico_id
    ): Response {
        /** @var CampoClinico $campoClinico */
        $campoClinico = $campoClinicoRepository->find($campo_clinico_id);
        $form         = $this->createForm(FormatoFofoeFileType::class, $campoClinico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->jsonResponse($campoClinicoManager->uploadFormatoFofoe($form->getData()));
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/formato/campo_clinico/{campo_clinico_id}/credenciales/show', methods: ['GET'], name: 'campo_clinico.credenciales.show')]
    public function showCredenciales(Request $request, int $campo_clinico_id): Response
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }
        if (!$this->isGrantedUserAccessToSolicitud($campoClinico->getSolicitud())) {
            $this->addFlash('danger', 'No puedes ver una solicitud de otra delegación');
            return $this->redirectToRoute('came.solicitud.index');
        }

        return $this->render('formatos/credenciales.html.twig', [
            'campo_clinico' => $campoClinico,
            'total'         => $campoClinico->getLugaresAutorizados(),
        ]);
    }

    #[Route('/formato/campo_clinico/{campo_clinico_id}/credenciales/download', methods: ['GET'], name: 'campo_clinico.credenciales.download', requirements: ['campo_clinico_id' => '\d+'])]
    public function downloadCredenciales(
        Request $request,
        GeneradorCredencialesInterface $generadorCredenciales,
        int $campo_clinico_id
    ): Response {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }
        if (!$this->isGrantedUserAccessToSolicitud($campoClinico->getSolicitud())) {
            $this->addFlash('danger', 'No puedes ver una solicitud de otra delegación');
            return $this->redirectToRoute('came.solicitud.index');
        }

        $overwrite = $request->query->get('overwrite', false);
        return $generadorCredenciales->responsePdf($this->credencialesDir, $campoClinico, $overwrite);
    }

    #[Route('/consulta/campo_clinico/{query}', methods: ['GET'], name: 'campo_clinico.consulta')]
    public function consulta(Request $request, string $query): Response
    {
        $data             = explode(':', base64_decode($query));
        $campo_clinico_id = (int) $data[0];
        $campoClinico     = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }

        return $this->render('campo_clinico/consulta.html.twig', [
            'campo_clinico' => $campoClinico,
            'total'         => $campoClinico->getLugaresAutorizados(),
            'index'         => $data[1],
        ]);
    }

    private function getCAMEorJDES(CampoClinico $campoClinico): mixed
    {
        /** @var Unidad $unidad */
        $unidad     = $campoClinico->getUnidad();
        $repository = $this->em->getRepository(Usuario::class);

        return $unidad?->getEsUmae()
            ? $repository->getJDESbyUnidad($unidad->getId())
            : $repository->getCamebyDelegacion($unidad->getDelegacion()->getId());
    }
}
