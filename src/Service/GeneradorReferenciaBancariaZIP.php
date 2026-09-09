<?php

namespace App\Service;

use App\Entity\Solicitud;
use App\Event\ReferenciaBancariaDownloadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class GeneradorReferenciaBancariaZIP implements GeneradorReferenciaBancariaZIPInterface
{
    public function __construct(
        private readonly EntityManagerInterface                $entityManager,
        private readonly GeneradorReferenciaBancariaPDFInterface $generadorReferenciaBancariaPDF,
        private readonly EventDispatcherInterface              $dispatcher,
        private readonly string                               $referenciasDir,
        private readonly string                               $zipDir,
    ) {}

    public function generarZipResponse(Solicitud $solicitud): Response
    {
        $files = $this->generadorReferenciaBancariaPDF->generarPDF($solicitud, $this->referenciasDir);

        $this->createZip($files);
        $response = $this->getZipResponse();
        $this->removeFiles();

        $this->dispatcher->dispatch(
            new ReferenciaBancariaDownloadedEvent($solicitud, ''),
            ReferenciaBancariaDownloadedEvent::NAME
        );

        return $response;
    }

    private function createZip(Finder $files): void
    {
        $zip = new ZipArchive();
        $zip->open($this->zipDir, ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFromString(basename($file), file_get_contents($file));
        }
        $zip->close();
    }

    private function getZipResponse(): Response
    {
        $response = new Response(file_get_contents($this->zipDir));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="ReferenciasBancaria.zip"');
        $response->headers->set('Content-length', filesize($this->zipDir));

        return $response;
    }

    private function removeFiles(): void
    {
        (new Filesystem())->remove($this->referenciasDir);
        unlink($this->zipDir);
    }
}
