<?php

namespace App\Controller\Fofoe\Enfermeria;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Factura;
use App\Entity\Pago;
use App\Form\FacturaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador FOFOE — Carga y descarga de facturas de Escuelas de Enfermería.
 */
#[Route('/fofoe/pagos')]
class FacturaController extends AbstractController
{
    /**
     * POST /fofoe/pagos/enfermeria/solicitud/{id}/factura
     * Sube la factura de un pago y marca el alumno como validado.
     */
    #[Route('/enfermeria/solicitud/{id}/factura', name: 'fofoe.pago.enfermeria.factura', methods: ['POST'])]
    public function upload(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var Pago $pago */
        $pago = $em->getRepository(Pago::class)->find($id);
        if (!$pago) {
            return new JsonResponse(['status' => false, 'message' => 'Pago no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(FacturaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Factura $factura */
            $factura = $form->getData();

            $pago->setFactura($factura);
            $pago->setFacturaGenerada(true);

            /** @var Alumno $alumno */
            $alumno = $pago->getEscuelaEnfermeriaSolicitud();
            $alumno->setStatus(Alumno::STATUS_VALIDATED);

            $em->persist($factura);
            $em->persist($pago);
            $em->persist($alumno);
            $em->flush();

            return new JsonResponse(['status' => true]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error al cargar la factura.',
            'errors'  => $this->getFormErrors($form),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * GET /fofoe/pagos/{id}/factura
     * Descarga de archivo de factura.
     */
    #[Route('/{id}/factura', name: 'fofoe.pago.enfermeria.factura.download', methods: ['GET'])]
    public function download(int $id, EntityManagerInterface $em): Response
    {
        /** @var Factura $factura */
        $factura = $em->getRepository(Factura::class)->find($id);
        if (!$factura) {
            throw $this->createNotFoundException('Factura no encontrada.');
        }

        $ruta = $this->getParameter('kernel.project_dir')
            . '/public/uploads/instituciones/facturas/'
            . $factura->getZip();

        if (!file_exists($ruta)) {
            throw $this->createNotFoundException('El archivo no existe en el servidor.');
        }

        $response = new BinaryFileResponse($ruta);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $factura->getZip());
        return $response;
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
}
