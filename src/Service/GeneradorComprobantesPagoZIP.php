<?php

namespace App\Service;

use App\Entity\Pago;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use ZipArchive;

class GeneradorComprobantesPagoZIP implements GeneradorComprobantePagosZIPInterface
{
    public function __construct(
        private readonly string                 $tmpDir,
        private readonly PropertyMappingFactory $propertyMappingFactory,
    ) {}

    public function generarZipResponse(array $pagos): Response
    {
        /** @var Pago $pago */
        $pago = $pagos[0];
        $zip  = $this->createZip($pago);

        foreach ($pagos as $pago) {
            /** @var Pago $pago */
            if (!$pago->getComprobantePago()) continue;

            $mapping  = $this->propertyMappingFactory->fromField($pago, 'comprobantePagoFile');
            $fileName = $pago->getComprobantePago();
            $file     = $mapping->getUploadDestination()
                . DIRECTORY_SEPARATOR
                . $mapping->getUploadDir($pago)
                . DIRECTORY_SEPARATOR
                . $fileName;

            $zip->addFromString($fileName, file_get_contents($file));
        }

        $zip->close();

        $response = $this->getZipResponse($pago);
        $this->removeFiles($pago);

        return $response;
    }

    private function getFileName(Pago $pago): string
    {
        $suffix = $pago->getSolicitud()?->getNoSolicitud()
            ?? $pago->getResidencia()?->getFolio()
            ?? '';

        return 'comprantePagos' . $suffix . '.zip';
    }

    private function createZip(Pago $pago): ZipArchive
    {
        $zip = new ZipArchive();
        $zip->open($this->tmpDir . $this->getFileName($pago), ZipArchive::CREATE);
        return $zip;
    }

    private function getZipResponse(Pago $pago): Response
    {
        $fileName = $this->getFileName($pago);
        $path     = $this->tmpDir . $fileName;

        $response = new Response(file_get_contents($path));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Content-length', filesize($path));

        return $response;
    }

    private function removeFiles(Pago $pago): void
    {
        (new Filesystem())->remove($this->tmpDir . $this->getFileName($pago));
    }
}
