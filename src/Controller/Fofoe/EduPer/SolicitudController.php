<?php

namespace App\Controller\Fofoe\EduPer;

use App\Controller\DIEControllerController;
use App\Entity\EduPer\Solicitud;
use App\Entity\Factura;
use App\Entity\Pago;
use App\Form\FacturaType;
use App\Service\EduPer\Presenter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\{BinaryFileResponse, JsonResponse, Request, Response, ResponseHeaderBag};
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class SolicitudController extends DIEControllerController
{
    #[Route('/fofoe/pagos/edu-per/solicitud/{type}', name: 'fofoe.pago.edu-per.index', methods: ['GET'])]
    public function index(Request $request, string $type): Response
    {
        return $this->render('edu_per/fofoe/index.html.twig', ['statusPago' => $request->query->get('estado', ''), 'type' => $type]);
    }

    #[Route('/fofoe/edu-per/solicitud/{type}', name: 'fofoe.edu-per.index', methods: ['GET'])]
    public function list(Request $request, string $type, Presenter $presenter): JsonResponse
    {
        $status = $request->query->get('estado', '');
        $items = $this->em->getRepository(Pago::class)->findEduPerByStatus($status, $type);
        return $this->json(['meta' => ['perPage' => count($items), 'page' => 1, 'total' => count($items)],
            'data' => array_map($presenter->pago(...), $items)]);
    }

    #[Route('/fofoe/edu-per/solicitud/{type}/{id}', name: 'fofoe.edu-per.show', methods: ['GET'])]
    public function show(string $type, int $id, Presenter $presenter): Response
    {
        $pago = $this->eduPerPago($id);
        if ($pago->getEduPerSolicitud()->getTipoSolicitud()->getCode() !== $type) { throw $this->createNotFoundException(); }
        return $this->render('edu_per/fofoe/show.html.twig', ['pago' => $presenter->pago($pago), 'type' => $type]);
    }

    #[Route('/fofoe/pagos/edu-per/solicitud/{id}/{status}', name: 'fofoe.pago.edu-per.update.pago', methods: ['POST'])]
    public function update(int $id, int $status, Request $request, MailerInterface $mailer,
        #[Autowire('%mailer_sender%')] string $mailerSender): JsonResponse
    {
        if (!in_array($status, [0, 1], true)) { return $this->json(['status' => false], 400); }
        $pago = $this->eduPerPago($id);
        $solicitud = $pago->getEduPerSolicitud();
        if ($status === 1) {
            $solicitud->setStatus($pago->isRequiereFactura() ? Solicitud::STATUS_INVOICE_PENDING : Solicitud::STATUS_VALIDATED);
            $solicitud->setHasDiferencia(false);
            $solicitud->setMontoDiferencia(0);
            $pago->setValidado(true);
        } else {
            $solicitud->setStatus(Solicitud::STATUS_REJECTED);
            $pago->setValidado(false);
            $pago->setObservaciones((string) $request->request->get('observaciones', ''));
            $hasDifference = filter_var($request->request->get('has_diferencia', false), FILTER_VALIDATE_BOOLEAN);
            $solicitud->setHasDiferencia($hasDifference);
            $solicitud->setMontoDiferencia($hasDifference ? (float) $request->request->get('monto_diferencia', 0) : 0);
        }
        $this->em->flush();
        try {
            $mailer->send((new Email())->from($mailerSender)->to($solicitud->getEmail())
                ->subject('Sistema de Administración del FOFOE - Información sobre su pago')
                ->html($this->renderView('emails/fofoe/edu-per/update_pago.html.twig', ['solicitud' => $solicitud, 'status' => $status])));
        } catch (\Throwable $e) { /* Payment status is already recorded. */ }
        return $this->json(['status' => true]);
    }

    #[Route('/fofoe/pagos/edu-per/solicitud/factura/{id}/store', name: 'fofoe.pago.edu-per.factura', methods: ['POST'])]
    public function factura(int $id, Request $request): JsonResponse
    {
        $pago = $this->eduPerPago($id);
        if (!$pago->isValidado() || !$pago->isRequiereFactura()) {
            return $this->json(['status' => false, 'message' => 'El pago aún no está aprobado para factura.'], 409);
        }
        $form = $this->createForm(FacturaType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json(['status' => false, 'message' => 'Error', 'errors' => $this->getFormErrors($form)], 400);
        }
        $factura = $form->getData();
        $pago->setFactura($factura);
        $pago->setFacturaGenerada(true);
        $pago->getEduPerSolicitud()->setStatus(Solicitud::STATUS_VALIDATED);
        $this->em->persist($factura);
        $this->em->flush();
        return $this->json(['status' => true]);
    }

    #[Route('/fofoe/pagos/edu-per/{id}/factura/download', name: 'fofoe.pago.edu-per.factura.download', methods: ['GET'])]
    public function downloadFactura(int $id, #[Autowire('%kernel.project_dir%')] string $projectDir): Response
    {
        $factura = $this->em->getRepository(Factura::class)->find($id);
        if (!$factura) { throw $this->createNotFoundException(); }
        $path = "$projectDir/public/uploads/instituciones/facturas/".basename((string) $factura->getZip());
        return $this->download($path, $factura->getZip());
    }

    #[Route('/fofoe/pagos/edu-per/cedula-fiscal/{id}', name: 'fofoe.pago.edu-per.cedula_fiscal', methods: ['GET'])]
    public function downloadCedula(int $id, #[Autowire('%kernel.project_dir%')] string $projectDir): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud || !$solicitud->getCedulaIdentificacion()) { throw $this->createNotFoundException(); }
        $filename = basename((string) $solicitud->getCedulaIdentificacion());
        return $this->download("$projectDir/public/uploads/edu-per/$id/$filename", $filename);
    }

    private function eduPerPago(int $id): Pago
    {
        $pago = $this->em->getRepository(Pago::class)->find($id);
        if (!$pago || !$pago->getEduPerSolicitud()) { throw $this->createNotFoundException('Pago no encontrado.'); }
        return $pago;
    }

    private function download(string $path, string $filename): Response
    {
        if (!is_file($path)) { throw $this->createNotFoundException('Archivo no encontrado.'); }
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        return $response;
    }
}
