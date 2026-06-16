<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Factura;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Solicitud;
use App\Exception\CouldNotFoundCedulaIdentificacionFiscal;
use App\Repository\PagoRepositoryInterface;
use App\Repository\SolicitudRepositoryInterface;
use App\Service\GeneradorFormatosFofoeZIPInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

#[Route('/ie')]
final class DocumentController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly SolicitudRepositoryInterface $solicitudRepository,
        private readonly DownloadHandler $downloadHandler,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/solicitud/{id}/descargar-comprobante-inscripcion', name: 'ie#descargar_comprobante_inscripcion')]
    public function descargarComprobanteInscripcion(int $id): Response
    {
        $this->checkInstitucion();

        $solicitud = $this->solicitudRepository->find($id);
        if (!$solicitud) {
            throw $this->createNotFindSolicitudException($id);
        }

        return $this->downloadHandler->downloadObject($solicitud, 'urlArchivoFile');
    }

    #[Route('/pagos/{id}/descargar-comprobante-de-pago', name: 'ie#descargar_comprobante_de_pago')]
    public function descargarComprobanteDePago(int $id, PagoRepositoryInterface $pagoRepository): Response
    {
        $this->checkInstitucion();

        /** @var Pago $pago */
        $pago = $pagoRepository->find($id);
        if (!$pago) {
            throw $this->createNotFindPagoException($id);
        }

        return $this->downloadHandler->downloadObject($pago, 'comprobantePagoFile');
    }

    #[Route('/descargar-cedula-de-identificacion-fiscal', name: 'ie#descargar_cedula_de_identificacion_fiscal')]
    public function descargarCedulaDeIdentificacionFiscal(): Response
    {
        $institucion = $this->checkInstitucion();

        if (!$institucion->getCedulaIdentificacion()) {
            throw CouldNotFoundCedulaIdentificacionFiscal::withInstitucionId($institucion->getId());
        }

        return $this->downloadHandler->downloadObject($institucion, 'cedulaFile');
    }

    #[Route('/descargar-cedula-de-identificacion-fiscal2', name: 'ie#descargar_cedula_de_identificacion_fiscal2')]
    public function descargarCedulaDeIdentificacionFiscal2(): Response
    {
        $institucion = $this->checkInstitucion();

        if (!$institucion->getCedulaIdentificacion2()) {
            throw CouldNotFoundCedulaIdentificacionFiscal::withInstitucionId($institucion->getId());
        }

        return $this->downloadHandler->downloadObject($institucion, 'cedulaFile2');
    }

    #[Route('/solicitudes/{id}/descargar-formatos-fofoe', name: 'ie#descargar_formatos_fofoe')]
    public function descargarFormatosFofoe(int $id, GeneradorFormatosFofoeZIPInterface $generadorFormatosFofoeZIP): Response
    {
        $this->checkInstitucion();

        $solicitud = $this->solicitudRepository->find($id);
        if (!$solicitud) {
            throw $this->createNotFindSolicitudException($id);
        }

        return $generadorFormatosFofoeZIP->generarZipResponse($solicitud);
    }

    #[Route('/formato/referencia_pago/{referencia}/show', methods: ['GET'], name: 'ie.refencia_pago.show')]
    public function showFormatoFofoe(string $referencia): Response
    {
        $institucion = $this->checkInstitucion();

        $solicitud = $this->em->getRepository(Solicitud::class)
            ->findOneBy(['referenciaBancaria' => $referencia]);

        $campos = [];

        if ($solicitud) {
            $campos = $solicitud->getCamposClinicos();
        } else {
            /** @var CampoClinico $campo */
            $campo     = $this->em->getRepository(CampoClinico::class)
                ->findOneBy(['referenciaBancaria' => $referencia]);
            $solicitud = $campo?->getSolicitud();
            $campos    = $campo ? [$campo] : [];
        }

        if (!$solicitud) {
            throw $this->createNotFoundException('Not found for reference ' . $referencia);
        }

        if ($institucion->getId() !== $solicitud->getInstitucion()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('ie/formato/solicitud/referencia_pago.html.twig', [
            'institucion' => $solicitud->getInstitucion(),
            'solicitud'   => $solicitud,
            'campos'      => $campos,
            'esPagoUnico' => $solicitud->getTipoPago() == Solicitud::TIPO_PAGO_UNICO,
            'referencia'  => $referencia,
        ]);
    }

    #[Route('/factura/{factura_id}/download', methods: ['GET'], name: 'ie.descargar.factura.download')]
    public function descargarFactura(int $factura_id): Response
    {
        $factura = $this->em->getRepository(Factura::class)->find($factura_id);

        if (!$factura) {
            throw $this->createNotFoundException('Not found for id ' . $factura_id);
        }

        return $this->downloadHandler->downloadObject($factura, 'zipFile');
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

    private function checkInstitucion(): Institucion
    {
        /** @var Institucion|null $institucion */
        $institucion = $this->getUser()->getInstitucion();

        if (!$institucion) {
            throw $this->createNotFindUserRelationWithInstitucionException();
        }

        return $institucion;
    }
}
