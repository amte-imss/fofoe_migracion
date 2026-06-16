<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Factura;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Solicitud;
use App\Exception\CouldNotFoundCedulaIdentificacionFiscal;
use App\Repository\InstitucionRepositoryInterface;
use App\Repository\PagoRepositoryInterface;
use App\Service\Fofoe\GeneradorExpedienteReferenciaZIPInterface;
use App\Service\Fofoe\GeneradorResumenReferenciaPagoPDFInterface;
use App\Service\GeneradorFormatoFofoeInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

#[Route('/fofoe')]
final class DocumentController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly DownloadHandler $downloadHandler,
        private readonly string $formatoFofoeDir,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pagos/{id}/descargar-comprobante-de-pago', name: 'fofoe#descargar_comprobante_de_pago')]
    public function descargarComprobanteDePago(int $id, PagoRepositoryInterface $pagoRepository): Response
    {
        /** @var Pago $pago */
        $pago = $pagoRepository->find($id);
        if (!$pago) {
            throw $this->createNotFindPagoException($id);
        }

        return $this->downloadHandler->downloadObject($pago, 'comprobantePagoFile');
    }

    #[Route('/instituciones/{id}/descargar-cedula-de-identificacion', name: 'fofoe#descargar_Cedula_Identificacion')]
    public function descargarCedulaIdentificacionFiscal(int $id, InstitucionRepositoryInterface $institucionRepository): Response
    {
        /** @var Institucion $institucion */
        $institucion = $institucionRepository->find($id);

        if (!$institucion) {
            throw $this->createNotFoundException('Not found for id ' . $id);
        }
        if (!$institucion->getCedulaIdentificacion()) {
            throw CouldNotFoundCedulaIdentificacionFiscal::withInstitucionId($institucion->getId());
        }

        return $this->downloadHandler->downloadObject($institucion, 'cedulaFile');
    }

    #[Route('/factura/{factura_id}/download', methods: ['GET'], name: 'factura.download')]
    public function downloadFile(int $factura_id): Response
    {
        $factura = $this->em->getRepository(Factura::class)->find($factura_id);

        if (!$factura) {
            throw $this->createNotFoundException('Not found for id ' . $factura_id);
        }

        return $this->downloadHandler->downloadObject($factura, 'zipFile');
    }

    #[Route('/solicitud/{solicitud_id}/oficio', methods: ['GET'], name: 'fofoe.solicitud.oficio_montos', requirements: ['solicitud_id' => '\d+'])]
    public function downloadOficioMontos(int $solicitud_id): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($solicitud_id);

        if (!$solicitud) {
            throw $this->createNotFoundException('Not found for id ' . $solicitud_id);
        }

        return $this->downloadHandler->downloadObject($solicitud, 'urlArchivoFile');
    }

    #[Route('/campo_clinico/{campo_clinico_id}/formato_fofoe/download', methods: ['GET'], name: 'fofoe.campo_clinico.formato_fofoe.download', requirements: ['campo_clinico_id' => '\d+'])]
    public function downloadFormatoFofoe(Request $request, GeneradorFormatoFofoeInterface $generadorFormatoFofoe, int $campo_clinico_id): Response
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }

        $overwrite        = $request->query->get('overwrite', false);
        $formatoFofoeFile = $generadorFormatoFofoe->responsePdf($this->formatoFofoeDir, $campoClinico, $overwrite);

        return $this->makePdfResponse($formatoFofoeFile, $generadorFormatoFofoe->getFileName($campoClinico));
    }

    #[Route('/referencia/{id_pago}/formato_referencia/show', methods: ['GET'], name: 'fofoe.referencia.formato_referencia.show', requirements: ['id_pago' => '\d+'])]
    public function showFormatoReferencia(Request $request, PagoRepositoryInterface $pagoRepository, int $id_pago): Response
    {
        $pago = $pagoRepository->find($id_pago);
        if (!$pago) {
            throw $this->createNotFoundException('Not found for id ' . $id_pago);
        }

        $pagos     = $pagoRepository->findBy(['referenciaBancaria' => $pago->getReferenciaBancaria()]);
        $campos    = $pagos[0]->getCamposPagados()['campos'];
        $solicitud = $pagos[0]->getSolicitud();

        $facturasUnicas = [];
        foreach (array_map(fn($p) => $p->getFactura(), $pagos) as $factura) {
            if (!$factura) continue;
            $facturasUnicas[$factura->getId()] = $factura;
        }

        return $this->render('formatos/resumen_expediente_CC.twig', [
            'pagos'     => $pagos,
            'solicitud' => $solicitud,
            'campos'    => $campos,
            'facturas'  => $facturasUnicas,
        ]);
    }

    #[Route('/referencia/{id_pago}/expediente/download', methods: ['GET'], name: 'fofoe.referencia.expediente_referencia.download', requirements: ['id_pago' => '\d+'])]
    public function downloadExpedienteZipReferencia(
        Request $request,
        PagoRepositoryInterface $pagoRepository,
        GeneradorExpedienteReferenciaZIPInterface $generadorExpedienteReferenciaZIP,
        int $id_pago
    ): Response {
        $pago = $pagoRepository->find($id_pago);
        if (!$pago) {
            throw $this->createNotFoundException('Not found for id ' . $id_pago);
        }

        return $generadorExpedienteReferenciaZIP->generarZipResponse($pago);
    }

    #[Route('/campo_clinico/{campo_clinico_id}/formato_fofoe_firmado/download', methods: ['GET'], name: 'fofoe.campo_clinico.formato_fofoe_firmado.download', requirements: ['campo_clinico_id' => '\d+'])]
    public function downloadFormatoFofoeFirmado(int $campo_clinico_id): Response
    {
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            throw $this->createNotFoundException('Not found for id ' . $campo_clinico_id);
        }

        return $this->downloadHandler->downloadObject($campoClinico, 'formatoFofoeFile');
    }

    #[Route('/referencia/{id_pago}/formato_referencia/download', methods: ['GET'], name: 'fofoe.referencia.formato_referencia.download', requirements: ['id_pago' => '\d+'])]
    public function downloadFormatoReferencia(
        Request $request,
        PagoRepositoryInterface $pagoRepository,
        GeneradorResumenReferenciaPagoPDFInterface $generadorResumenReferenciaPago,
        int $id_pago
    ): Response {
        $pago = $pagoRepository->find($id_pago);
        if (!$pago) {
            throw $this->createNotFoundException('Not found for id ' . $id_pago);
        }

        $resumenReferenciaFile = $generadorResumenReferenciaPago->responsePdf($this->formatoFofoeDir, $pago);

        return $this->makePdfResponse($resumenReferenciaFile, $generadorResumenReferenciaPago->getFileName($pago));
    }

    private function makePdfResponse(string $file, string $nameFileResponse): Response
    {
        $fileContent = file_get_contents($file);
        $filesize    = filesize($file);
        unlink($file);

        return new Response($fileContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment;filename="' . $nameFileResponse . '"',
            'Content-length'      => $filesize,
        ]);
    }

    private function pdfResponse(string $fileName, string $contentDisposition = 'attachment'): BinaryFileResponse
    {
        $valid = [ResponseHeaderBag::DISPOSITION_INLINE, ResponseHeaderBag::DISPOSITION_ATTACHMENT];

        if (!in_array($contentDisposition, $valid, strict: true)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected one of the following directives: "%s", but "%s" given.',
                implode('", "', $valid),
                $contentDisposition
            ));
        }

        $response = new BinaryFileResponse($fileName);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }
}
