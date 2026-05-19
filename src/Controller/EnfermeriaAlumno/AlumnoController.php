<?php

namespace App\Controller\EnfermeriaAlumno;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Entity\Pago;
use App\Form\EnfermeriaAlumno\AlumnoLoginType;
use App\Form\EnfermeriaAlumno\ComprobantePagoType;
use App\Repository\PagoRepository;
use App\Service\GeneradorRefenciaBancaria2025;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/enfermeria-alumno')]
class AlumnoController extends AbstractController
{
    /**
     * GET|POST /enfermeria-alumno/login/{solicitudId}
     * Login del alumno con CURP, email y captcha.
     */
    #[Route('/login/{solicitudId}', name: 'enfermeria_alumno.login', methods: ['GET', 'POST'])]
    public function login(
        int                    $solicitudId,
        Request                $request,
        EntityManagerInterface $em
    ): Response {
        $solicitud = $em->getRepository(Solicitud::class)->find($solicitudId);
        if (!$solicitud) {
            throw $this->createNotFoundException('Solicitud no encontrada.');
        }

        $form          = $this->createForm(AlumnoLoginType::class);
        $errorCaptcha  = false;
        $errorLogin    = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                // TODO: Reactivar validación de captcha cuando se configure el bundle
                $alumno = $em->getRepository(Alumno::class)->findOneBy([
                    'email'     => $data['email'],
                    'curp'      => $data['curp'],
                    'solicitud' => $solicitudId,
                ]);

                if (!$alumno) {
                    $errorLogin = 'Credenciales incorrectas.';
                    $form->addError(new FormError('Credenciales incorrectas.'));
                } else {
                    $request->getSession()->set('enfermeria_alumno_id', $alumno->getId());
                    return $this->redirectToRoute('enfermeria_alumno.index');
                }
            } else {
                $errorLogin = 'Credenciales incorrectas.';
                $form->addError(new FormError('Credenciales incorrectas.'));
            }
        }

        return $this->render('enfermeria-alumno/login.html.twig', [
            'solicitud'     => $solicitud,
            'form'          => $form->createView(),
            'error_captcha' => $errorCaptcha,
            'error_login'   => $errorLogin,
        ]);
    }

    #[Route('/logout', name: 'enfermeria_alumno.logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('enfermeria_alumno_id');
        return $this->render('enfermeria-alumno/sin_sesion.html.twig');
    }

    /**
     * GET /enfermeria-alumno/
     * Dashboard del alumno autenticado.
     */
    #[Route('/', name: 'enfermeria_alumno.index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $alumno = $this->getAlumnoFromSession($request, $em);
        if (!$alumno) {
            // Sin sesión activa de alumno: mostrar página de acceso no válido
            return $this->render('enfermeria-alumno/sin_sesion.html.twig');
        }

        return $this->render('enfermeria-alumno/index.html.twig', [
            'alumno' => $alumno,
        ]);
    }

    /**
     * GET /enfermeria-alumno/carga-comprobante
     * Vista de carga de comprobante de pago.
     */
    #[Route('/carga-comprobante', name: 'enfermeria_alumno.carga_comprobante', methods: ['GET'])]
    public function cargaComprobante(Request $request, EntityManagerInterface $em): Response
    {
        $alumno = $this->getAlumnoFromSession($request, $em);
        if (!$alumno) {
            return $this->render('enfermeria-alumno/sin_sesion.html.twig');
        }

        return $this->render('enfermeria-alumno/carga.html.twig', [
            'alumno' => $alumno,
        ]);
    }

    /**
     * GET /enfermeria-alumno/referencia
     * Descarga el PDF de referencia bancaria.
     */
    #[Route('/referencia', name: 'enfermeria_alumno.referencia', methods: ['GET'])]
    public function downloadReferencia(
        Request                          $request,
        EntityManagerInterface           $em,
        GeneradorRefenciaBancaria2025    $generadorReferencia,
        Pdf                              $pdf
    ): Response {
        $alumno = $this->getAlumnoFromSession($request, $em);
        if (!$alumno) {
            return $this->render('enfermeria-alumno/sin_sesion.html.twig');
        }

        /** @var PagoRepository $pagoRepository */
        $pagoRepository = $em->getRepository(Pago::class);

        try {
            if ($alumno->getStatus() === Alumno::STATUS_REJECTED_DOCUMENTACION) {
                $lastPago  = $alumno->getLastPago();
                $referencia = $lastPago->getReferenciaBancaria();
            } else {
                $pago = $pagoRepository->getPagoPendienteByEscuelaEnfermeria($alumno->getId());
                $referencia = $pago->getReferenciaBancaria();
            }
        } catch (\Throwable $e) {
            // Crear nuevo pago si no existe uno pendiente
            $referencia = $generadorReferencia->generateNextReference();
            $pago = new Pago();
            $pago->setEscuelaEnfermeriaSolicitud($alumno);
            $pago->setReferenciaBancaria($referencia);
            $pago->setFechaCreacion(Carbon::now());
            $pago->setMonto(
                $alumno->hasDiferencia()
                    ? $alumno->getMontoDiferencia()
                    : $alumno->getMonto()
            );
            $em->persist($pago);
            $em->flush();
        }

        $referenciasDir = $this->getParameter('kernel.project_dir') . '/var/referencias/enfermeria/' . $alumno->getSolicitud()->getId();
        if (!is_dir($referenciasDir)) {
            mkdir($referenciasDir, 0775, true);
        }
        $filename = $alumno->getId() . '_referenciaPago.pdf';
        $path     = $referenciasDir . '/' . $filename;

        $pdf->generateFromHtml(
            $this->renderView('formatos/enfermeria/referencia.html.twig', [
                'user'       => $alumno,
                'solicitud'  => $alumno->getSolicitud(),
                'referencia' => $referencia,
            ]),
            $path,
            ['page-size' => 'Letter', 'encoding' => 'utf-8'],
            true
        );

        // Actualizar estado si era inicio
        if (in_array($alumno->getStatus(), [Alumno::STATUS_INICIO, Alumno::STATUS_REJECTED], true)) {
            $alumno->setStatus(Alumno::STATUS_EN_ESPERA_PAGO);
            $em->flush();
        }

        $fileContent = file_get_contents($path);
        $filesize    = strlen($fileContent);
        @unlink($path);

        return new Response($fileContent, Response::HTTP_OK, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Content-Length'      => $filesize,
        ]);
    }

    /**
     * POST /enfermeria-alumno/carga-de-comprobante-de-pago
     * Guarda el comprobante de pago cargado por el alumno.
     */
    #[Route('/carga-de-comprobante-de-pago', name: 'enfermeria_alumno.cargar_comprobante', methods: ['POST'])]
    public function cargarComprobante(
        Request                $request,
        EntityManagerInterface $em,
        MailerInterface        $mailer
    ): JsonResponse {
        $alumno = $this->getAlumnoFromSession($request, $em);
        if (!$alumno) {
            return new JsonResponse(['status' => false, 'message' => 'No autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var PagoRepository $pagoRepository */
        $pagoRepository = $em->getRepository(Pago::class);

        try {
            $pago = $alumno->getStatus() === Alumno::STATUS_REJECTED_DOCUMENTACION
                ? $alumno->getLastPago()
                : $pagoRepository->getPagoPendienteByEscuelaEnfermeria($alumno->getId());
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontró pago pendiente.'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ComprobantePagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Pago $pago */
            $pago = $form->getData();

            if ($alumno->getStatus() === Alumno::STATUS_REJECTED_DOCUMENTACION) {
                $pago->setValidado(null);
            }

            // Cédula fiscal (si requiere factura)
            $cedulaField = $form->get('cedulaFile');
            if ($pago->isRequiereFactura() && (!$cedulaField || !$cedulaField->getData())) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Para facturación es necesaria la Cédula de Identificación Fiscal.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($pago->isRequiereFactura() && $cedulaField && $cedulaField->getData()) {
                $file    = $cedulaField->getData();
                $destDir = $this->getParameter('kernel.project_dir') . '/public/uploads/alumno_enfermeria/' . $alumno->getId() . '/';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0775, true);
                }
                $nombreArchivo = 'identificacion-fiscal.' . $file->guessExtension();
                $file->move($destDir, $nombreArchivo);
                $alumno->setCedulaIdentificacion($nombreArchivo);
                $em->persist($alumno);
            }

            $pago->setFechaPago($pago->getFechaPagoRegistrada());
            $em->persist($pago);
            $alumno->setStatus(Alumno::STATUS_ESPERA_VALIDACION);
            $em->persist($alumno);
            $em->flush();

            return new JsonResponse([
                'status' => true,
                'data'   => ['id' => $pago->getId()],
            ]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error al guardar el comprobante.',
            'errors'  => $this->getFormErrors($form),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * GET /enfermeria-alumno/cedula-fiscal/{id}
     * Descarga cédula de identificación fiscal del alumno.
     */
    #[Route('/cedula-fiscal/{id}', name: 'enfermeria_alumno.cedula_fiscal', methods: ['GET'])]
    public function downloadCedula(int $id, EntityManagerInterface $em): Response
    {
        $alumno = $em->getRepository(Alumno::class)->find($id);
        if (!$alumno || !$alumno->getCedulaIdentificacion()) {
            throw $this->createNotFoundException('Cédula no encontrada.');
        }

        $ruta = $this->getParameter('kernel.project_dir')
            . '/public/uploads/alumno_enfermeria/' . $alumno->getId() . '/'
            . $alumno->getCedulaIdentificacion();

        if (!file_exists($ruta)) {
            throw $this->createNotFoundException('El archivo no existe en el servidor.');
        }

        $response = new BinaryFileResponse($ruta);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $alumno->getCedulaIdentificacion());
        return $response;
    }

    /**
     * GET /enfermeria-alumno/{id}/factura
     * Descarga factura del pago.
     */
    #[Route('/{id}/factura', name: 'enfermeria_alumno.factura.download', methods: ['GET'])]
    public function downloadFactura(int $id, EntityManagerInterface $em): Response
    {
        $factura = $em->getRepository(\App\Entity\Factura::class)->find($id);
        if (!$factura) {
            throw $this->createNotFoundException('Factura no encontrada.');
        }

        $ruta = $this->getParameter('kernel.project_dir') . '/public/uploads/instituciones/facturas/' . $factura->getZip();
        if (!file_exists($ruta)) {
            throw $this->createNotFoundException('El archivo no existe en el servidor.');
        }

        $response = new BinaryFileResponse($ruta);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $factura->getZip());
        return $response;
    }

    // -------------------------------------------------------------------------

    private function getAlumnoFromSession(Request $request, EntityManagerInterface $em): ?Alumno
    {
        $alumnoId = $request->getSession()->get('enfermeria_alumno_id');
        if (!$alumnoId) {
            return null;
        }
        return $em->getRepository(Alumno::class)->find($alumnoId);
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
