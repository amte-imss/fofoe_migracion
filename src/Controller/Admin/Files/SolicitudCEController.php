<?php

namespace App\Controller\Admin\Files;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/admin/files', name: 'admin.files.')]
class SolicitudCEController extends DIEControllerController
{
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/solicitud-ce/{id}', name: 'solicitud-ce.show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id)
            ?? throw $this->createNotFoundException('Solicitud no encontrada');

        return new JsonResponse([
            'data' => $this->normalizer->normalize($solicitud, 'json', [
                AbstractNormalizer::ATTRIBUTES => [
                    'id', 'noSolicitud', 'referenciaBancaria', 'monto', 'estatus', 'fecha', 'validado',
                    'observaciones', 'validateOficioMontos', 'motiveOficioMontos',
                    'campus' => ['id', 'nombre'],
                    'camposClinicos' => [
                        'id', 'fechaInicialFormatted', 'fechaFinalFormatted', 'lugaresSolicitados', 'lugaresAutorizados',
                        'referenciaBancaria', 'monto', 'estatus', 'unidad' => ['id', 'nombre'],
                        'validateFormatoFofoe', 'motiveFormatoFofoe',
                    ],
                ],
            ]),
        ]);
    }

    #[Route('/solicitud-ce/{id}/oficio-montos/preview', name: 'solicitud-ce.oficio-montos.preview', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function previewOficioMontos(int $id): BinaryFileResponse
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id)
            ?? throw $this->createNotFoundException('Not found for id ' . $id);

        return $this->inlinePdfResponse($solicitud, 'urlArchivoFile', $id);
    }

    #[Route('/campo-clinico/{id}/formato-fofoe/preview', name: 'campo-clinico.formato-fofoe.preview', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function previewFormatoFofoe(int $id): BinaryFileResponse
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($id)
            ?? throw $this->createNotFoundException('Not found for id ' . $id);

        return $this->inlinePdfResponse($campoClinico, 'formatoFofoeFile', $id);
    }

    #[Route('/solicitud-ce/{id}/oficio-montos-upload', name: 'solicitud-ce.oficio-montos.upload', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadOficioMontos(Request $request, int $id): JsonResponse
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            return $this->httpErrorResponse('Solicitud no encontrada', Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('comprobante'); // <input name="comprobante">
        if (!$file instanceof UploadedFile) {
            return $this->httpErrorResponse('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        // Size 0 usually means the upload exceeded upload_max_filesize
        if ($file->getSize() <= 0) {
            return $this->httpErrorResponse('El archivo es demasiado grande', Response::HTTP_BAD_REQUEST);
        }

        $solicitud->setUrlArchivoFile($file);
        $this->em->flush();

        return $this->successResponse('OK');
    }

    #[Route('/solicitud-ce/{id}/cancel', name: 'solicitud-ce.cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancelSolicitud(int $id): JsonResponse
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            return $this->httpErrorResponse('Solicitud no encontrada', Response::HTTP_BAD_REQUEST);
        }

        $solicitud->setEstatus(Solicitud::CANCELADA);

        /** @var CampoClinico $campoClinico */
        foreach ($solicitud->getCampoClinicos() as $campoClinico) {
            $campoClinico->setLugaresAutorizados(0);
            $campoClinico->setObsValRegistro('Cancelada por el administrador');
        }

        $this->em->flush();

        return $this->successResponse('Solicitud cancelada exitosamente');
    }

    #[Route('/solicitud-ce/{id}/status-validacion-montos', name: 'solicitud-ce.status-validacion-montos', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function changeValidacionMontos(int $id): JsonResponse
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            return $this->httpErrorResponse('Solicitud no encontrada', Response::HTTP_BAD_REQUEST);
        }

        $solicitud->setEstatus(Solicitud::EN_VALIDACION_DE_MONTOS_CAME);
        $this->em->flush();

        // TODO: original message says "cancelada" — likely a copy/paste leftover
        return $this->successResponse('Solicitud cancelada exitosamente');
    }

    #[Route('/campo-clinico/{id}/formato-fofoe-upload', name: 'campo-clinico.formato-fofoe.upload', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadFormatoFofoe(Request $request, int $id): JsonResponse
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($id);
        if (!$campoClinico) {
            return $this->httpErrorResponse('Campo clínico no encontrado', Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('comprobante'); // <input name="comprobante">
        if (!$file instanceof UploadedFile) {
            return $this->httpErrorResponse('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() <= 0) {
            return $this->httpErrorResponse('Archivo con peso mayor al permitido de 2MB', Response::HTTP_BAD_REQUEST);
        }

        try {
            $campoClinico->setFormatoFofoeFile($file);
            $this->em->flush();
        } catch (\Exception $e) {
            return $this->failedResponse('Error uploading file: ' . $e->getMessage());
        }

        return $this->successResponse('OK');
    }

    /**
     * Streams a Vich-managed file inline (no download) as PDF.
     */
    private function inlinePdfResponse(object $entity, string $fileField, int $id): BinaryFileResponse
    {
        $path = $this->storage->resolvePath($entity, $fileField);

        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException('File not found for id ' . $id);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($path));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
