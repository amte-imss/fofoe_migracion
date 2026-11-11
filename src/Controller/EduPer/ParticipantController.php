<?php

namespace App\Controller\EduPer;

use App\Controller\DIEControllerController;
use App\Entity\EduPer\Solicitud;
use App\Entity\Factura;
use App\Entity\Pago;
use App\Form\Type\EduPer\ComprobantePagoType;
use App\Repository\PagoRepositoryInterface;
use App\Service\EduPer\Presenter;
use App\Service\GeneradorRefenciaBancaria2025;
use App\Service\UploaderComprobantePagoInterface;
use Knp\Snappy\Pdf;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\{BinaryFileResponse, JsonResponse, Request, Response, ResponseHeaderBag};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/edu-per/participant')]
class ParticipantController extends DIEControllerController
{
    #[Route('/', name: 'edu-per.participant.index', methods: ['GET'])]
    public function index(Presenter $presenter): Response
    {
        return $this->render('edu_per/participant/index.html.twig', ['solicitud' => $presenter->solicitud($this->participant(), true)]);
    }

    #[Route('/carga-comprobante', name: 'edu-per.participant.carga-comprobante', methods: ['GET'])]
    public function carga(Presenter $presenter): Response
    {
        return $this->render('edu_per/participant/carga.html.twig', ['usuario' => $presenter->solicitud($this->participant())]);
    }

    #[Route('/referencia', name: 'edu-per.participant.download.referencia', methods: ['GET'])]
    public function referencia(
        Pdf $pdf,
        PagoRepositoryInterface $pagos,
        GeneradorRefenciaBancaria2025 $generator,
        EntrypointLookupInterface $entrypoints,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response
    {
        $user = $this->participant();
        $pago = $pagos->getPagoPendienteByEduPerm($user->getId());
        if (!$pago) {
            $pago = new Pago();
            $pago->setEduPerSolicitud($user);
            $pago->setReferenciaBancaria($generator->generateNextReference());
            $pago->setFechaCreacion(new \DateTime());
            $pago->setMonto($user->hasDiferencia() ? $user->getMontoDiferencia() : $user->getAmount());
            $this->em->persist($pago);
        }
        if (in_array($user->getStatus(), [Solicitud::STATUS_INICIO, Solicitud::STATUS_REJECTED], true)) {
            $user->setStatus(Solicitud::STATUS_EN_ESPERA_PAGO);
        }
        $this->em->flush();
        $css = '';
        foreach (['layout-formato-fofoe', 'layout'] as $entry) {
            foreach ($entrypoints->getCssFiles($entry) as $url) {
                $path = realpath($projectDir.'/public/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/'));
                if ($path && str_starts_with($path, realpath($projectDir.'/public').DIRECTORY_SEPARATOR)) {
                    $css .= file_get_contents($path)."\n";
                }
            }
        }
        // The layout bundle imports a remote font; wkhtmltopdf must not make HTTP requests while PHP serves the PDF.
        $css = preg_replace('~@import\s+url\([^)]*\);~i', '', $css);
        $logo = base64_encode(file_get_contents($projectDir.'/assets/images/logo_IMSS.png'));
        $bytes = $pdf->getOutputFromHtml($this->renderView('formatos/edu-per/referencia.html.twig', [
            'user' => $user, 'solicitud' => $user, 'referencia' => $pago->getReferenciaBancaria(),
            'pdf_css' => $css, 'logo_data_uri' => 'data:image/png;base64,'.$logo,
        ]), ['page-size' => 'Letter', 'encoding' => 'utf-8']);
        return new Response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$user->getId().'_referenciaPago.pdf"',
        ]);
    }

    #[Route('/carga-de-comprobante-de-pago', name: 'edu-per.participant.pago.carga_de_comprobante_de_pago', methods: ['POST'])]
    public function subir(Request $request, PagoRepositoryInterface $pagos,
        UploaderComprobantePagoInterface $uploader,
        #[Autowire('%kernel.project_dir%')] string $projectDir): JsonResponse
    {
        $user = $this->participant();
        if (!in_array($user->getStatus(), [Solicitud::STATUS_INICIO, Solicitud::STATUS_EN_ESPERA_PAGO, Solicitud::STATUS_REJECTED], true)) {
            return $this->json(['status' => false, 'message' => 'El pago ya está en revisión.'], 409);
        }
        $pago = $pagos->getPagoPendienteByEduPerm($user->getId());
        if (!$pago) { return $this->json(['status' => false, 'message' => 'Primero descargue la referencia de pago.'], 404); }
        $form = $this->createForm(ComprobantePagoType::class, $pago);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cedula = $form->get('cedulaFile');
            if ($pago->isRequiereFactura() && !$cedula->getData()) {
                $cedula->addError(new FormError('Para la emisión de la factura es necesaria la Cédula de Identificación Fiscal'));
            } else {
                if ($pago->isRequiereFactura()) {
                    $file = $cedula->getData();
                    $directory = "$projectDir/public/uploads/edu-per/{$user->getId()}";
                    if (!is_dir($directory)) { mkdir($directory, 0775, true); }
                    $filename = 'identificacion-fiscal.'.$file->guessExtension();
                    $file->move($directory, $filename);
                    $user->setCedulaIdentificacion($filename);
                }
                $pago->setFechaPago($pago->getFechaPagoRegistrada());
                $pago->setValidado(null);
                $user->setStatus(Solicitud::STATUS_ESPERA_VALIDACION);
                $uploader->update($pago);
                $this->em->flush();
                return $this->json(['status' => true, 'data' => ['id' => $pago->getId()]]);
            }
        }
        return $this->json(['status' => false, 'message' => 'Error', 'errors' => $this->getFormErrors($form)], 400);
    }

    #[Route('/factura/{id}', name: 'edu-per.participant.factura', methods: ['GET'])]
    public function factura(int $id, #[Autowire('%kernel.project_dir%')] string $projectDir): Response
    {
        $factura = $this->em->getRepository(Factura::class)->find($id);
        $user = $this->participant();
        if (!$factura || !$user->getLastPago() || $user->getLastPago()->getFactura()?->getId() !== $id) {
            throw $this->createNotFoundException();
        }
        $filename = basename((string) $factura->getZip());
        $path = "$projectDir/public/uploads/instituciones/facturas/$filename";
        if (!is_file($path)) { throw $this->createNotFoundException('Archivo no encontrado.'); }
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        return $response;
    }

    #[Route('/logout', name: 'edu-per.participant.logout', methods: ['GET'])]
    public function logout(): never { throw new \LogicException('Intercepted by the security firewall.'); }

    private function participant(): Solicitud
    {
        $user = $this->getUser();
        if (!$user instanceof Solicitud) { throw $this->createAccessDeniedException(); }
        return $user;
    }
}
