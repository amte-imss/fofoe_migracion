<?php

namespace App\Controller\Residente;

use App\Controller\DIEControllerController;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\Residente;
use App\Entity\Usuario;
use App\Repository\PagoRepositoryInterface;
use App\Security\Voter\ResidenciaVoter;
use App\Service\GeneradorComprobantePagosZIPInterface;
use App\Service\Residente\GeneradorReferenciaPagoResidentePDFInterface;
use iio\libmergepdf\Merger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\DownloadHandler;

#[Route('/residente')]
#[IsGranted('ROLE_ALUMNO_POSGRADO')]
final class DocumentController extends DIEControllerController
{
    #[Route('/residencia/{id:residencia}/referencia-pago/download', name: 'residente.descargar_referencia_pago', methods: ['GET'])]
    public function descargarReferenciaPago(
        Residencia $residencia,
        GeneradorReferenciaPagoResidentePDFInterface $generadorReferenciaPagoResidentePDF,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response {
        $this->denyAccessUnlessGranted(ResidenciaVoter::DESCARGAR_REFERENCIA_BANCARIA, $residencia);

        $merger = new Merger();
        $merger->addRaw($generadorReferenciaPagoResidentePDF->generarPDF($residencia));
        $merger->addFile($projectDir . '/assets/files/Consulta_Tipo_de_Cambio_FOFOE.pdf');

        $filename = $residencia->getFolio() . '-' . $residencia->getCiclo() . '.pdf';

        return new Response($merger->merge(), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $filename,
                $this->asciiFallback($filename)
            ),
        ]);
    }

    #[Route('/residencia/{id:residencia}/referencia-pago/show', name: 'residente.mostrar_referencia_pago', methods: ['GET'])]
    public function showReferenciaPago(Residencia $residencia): Response
    {
        $this->denyAccessUnlessGranted(ResidenciaVoter::DESCARGAR_REFERENCIA_BANCARIA, $residencia);

        $pago = $residencia->getPagos()->count() > 0 ? $residencia->getLastPago() : null;

        return $this->render('residente/formato/residencia/referencia_pago_ex_imss.html.twig', [
            'residencia' => $residencia,
            'residente' => $residencia->getResidente(),
            'pago' => $pago,
            'referencia' => $pago?->getReferenciaBancaria() ?? '',
        ]);
    }

    #[Route('/residencia/{id:residencia}/comprobantes-pago/download', name: 'residente.descargar_all_comprobantes_de_pago', methods: ['GET'])]
    public function descargarAllComprobantesDePago(
        Residencia $residencia,
        GeneradorComprobantePagosZIPInterface $generadorComprobantePagosZIP,
    ): Response {
        $this->getResidenteOrFail();
        $this->denyAccessUnlessGranted(ResidenciaVoter::DESCARGAR_COMPROBANTE_DE_PAGO, $residencia);

        $lastPago = $residencia->getLastPago();

        if (1 === $residencia->getPagos()->count() && !$lastPago->getComprobantePago()) {
            throw $this->createNotFindPagoException($lastPago->getId());
        }

        return $generadorComprobantePagosZIP->generarZipResponse($residencia->getPagos()->toArray());
    }

    #[Route('/residencia/{id:residencia}/comprobante-pago/{idPago}/download', name: 'residente.descargar_comprobantes_de_pago', methods: ['GET'], requirements: ['idPago' => '\d+'])]
    public function descargarComprobanteDePago(
        Residencia $residencia,
        int $idPago,
        PagoRepositoryInterface $pagoRepository,
        DownloadHandler $downloadHandler,
    ): Response {
        $this->getResidenteOrFail();
        $this->denyAccessUnlessGranted(ResidenciaVoter::DESCARGAR_COMPROBANTE_DE_PAGO, $residencia);

        $pago = $pagoRepository->findOneBy(['id' => $idPago, 'residencia' => $residencia]);

        if (!$pago) {
            throw $this->createNotFindPagoException($idPago);
        }

        return $downloadHandler->downloadObject($pago, 'comprobantePagoFile');
    }

    private function getResidenteOrFail(): Residente
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        return $usuario->getResidente() ?? throw $this->createNotFindUserRelationWithResidenteException();
    }

    private function asciiFallback(string $filename): string
    {
        return preg_replace('/[^\x20-\x7E]|[\/\\\\%"]/', '_', $filename);
    }
}
