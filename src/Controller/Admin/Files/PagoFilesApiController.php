<?php

namespace App\Controller\Admin\Files;

use App\Entity\Pago;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/admin/files/pagos/{id}', requirements: ['id' => '\d+'])]
#[IsGranted('ROLE_SUPER')]
final class PagoFilesApiController extends AbstractController
{
    private const int MAX_RECEIPT_SIZE = 3 * 1024 * 1024;

    private const array SERIALIZED_ATTRIBUTES = [
        'id', 'type', 'monto', 'fechaPago', 'referenciaBancaria', 'validado',
        'requiereFactura', 'observaciones', 'tipoMoneda', 'fechaPagoRegistrada',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {}

    #[Route('', name: 'admin.files.pagos.show', methods: ['GET'])]
    public function show(#[MapEntity(id: 'id')] Pago $pago): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->normalizer->normalize($pago, 'json', [
                'attributes' => self::SERIALIZED_ATTRIBUTES,
            ]),
        ]);
    }

    #[Route('/comprobante', name: 'admin.files.pagos.comprobante', methods: ['GET'])]
    public function receipt(#[MapEntity(id: 'id')] Pago $pago): BinaryFileResponse
    {
        $path = $this->storage->resolvePath($pago, 'comprobantePagoFile');

        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException(sprintf('File not found for id %d', $pago->getId()));
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($path));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    #[Route('/comprobante-upload', name: 'admin.files.pagos.comprobante.upload', methods: ['POST'])]
    public function uploadReceipt(Request $request, #[MapEntity(id: 'id')] Pago $pago): JsonResponse
    {
        $file = $request->files->get('comprobante');

        if (!$file instanceof UploadedFile) {
            return new JsonResponse([
                'status' => false,
                'message' => 'No file uploaded (field "comprobante")',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() > self::MAX_RECEIPT_SIZE) {
            return new JsonResponse([
                'status' => false,
                'message' => 'File too large',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Assigning the Vich File property triggers the upload on flush
        $pago->setComprobantePagoFile($file);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'OK',
        ]);
    }
}
