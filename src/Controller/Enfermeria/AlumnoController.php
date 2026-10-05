<?php

namespace App\Controller\Enfermeria;

use App\Controller\DIEControllerController;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Entity\Factura;
use App\Entity\Pago;
use App\Entity\Usuario;
use App\Form\Type\EnfermeriaAlumno\AlumnoLoginType;
use App\Form\Type\EnfermeriaAlumno\ComprobantePagoType;
use App\Repository\PagoRepositoryInterface;
use App\Service\GeneradorRefenciaBancaria2025;
use App\Service\UploaderComprobantePagoInterface;
use Carbon\Carbon;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


#[Route('/enfermeria-alumno')]
class AlumnoController extends DIEControllerController
{
    public function menu(): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $this->render('enfermeria-alumno/_menu.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/login/{solicitudId}', methods: ['GET', 'POST'], name: 'enfermeria-alumno.login')]
    public function login(
        Request $request,
        Security $security,
        int $solicitudId
    ): Response {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($solicitudId);
        if (!$solicitud) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $formulario = $this->createForm(AlumnoLoginType::class);
        $error_captcha = false;
        $errors_login = null;

        if ($request->getMethod() === 'POST') {
            $formulario->handleRequest($request);
            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $data = $formulario->getData();
                if ($request->getSession()->get('captcha_phrase') == $data['captcha']) {
                    $resultSet = $this->em->getRepository(Alumno::class)->findBy([
                        'email' => $data['email'],
                        'curp' => $data['curp'],
                        'solicitud' => $solicitudId,
                    ]);
                    $user = count($resultSet) ? $resultSet[0] : null;
                    if (!$user) {
                        $errors_login = 'Credenciales incorrectas';
                        $formulario->addError(new FormError('Credenciales incorrectas'));
                    } else {
                        //$token = new UsernamePasswordToken($user, 'enfermeria_alumno', $user->getRoles());
                        $security->login($user, firewallName: 'enfermeria_alumno');
                       // $tokenStorage->setToken($token);

                        return $this->redirect($this->generateUrl('enfermeria-alumno.index'));
                    }
                } else {
                    $error_captcha = true;
                    $formulario->addError(new FormError('El código no coincide con el que ingresaste. Por favor vuelve a intentarlo o genere uno nuevo.'));
                }
            } else {
                $formulario->addError(new FormError('Credenciales incorrectas'));
                $errors_login = 'Credenciales incorrectas';
            }
        }

        return $this->render('enfermeria-alumno/login.html.twig', [
            'solicitud' => $solicitud,
            'errors' => $this->getFormErrors($formulario),
            'error_captcha' => $error_captcha,
            'error_login' => $errors_login,
        ]);
    }

    #[Route('/', methods: ['GET'], name: 'enfermeria-alumno.index')]
    public function index(Request $request): Response
    {
        $user = $this->serializerUser();

        return $this->render('enfermeria-alumno/index.html.twig', [
            'usuario' => $user,
        ]);
    }

    #[Route('/carga-comprobante', methods: ['GET'], name: 'enfermeria-alumno.carga-comprobante')]
    public function cargaComprobante(): Response
    {
        $user = $this->serializerUser();

        return $this->render('enfermeria-alumno/carga.html.twig', [
            'usuario' => $user,
        ]);
    }

    #[Route('/referencia', methods: ['GET'], name: 'enfermeria-alumno.download.referencia')]
    public function downloadReferencia(
        Pdf $pdf,
        EntityManagerInterface $entityManager,
        GeneradorRefenciaBancaria2025 $generadorRefenciaBancaria2025,
        Request $request,
        #[Autowire('%referencias_bancarias_dir%')] string $referenciasPath,
    ): Response {
        /** @var Alumno $user */
        $user = $this->getUser();
        $filename = $user->getId() . '_referenciaPago.pdf';
        $path = "$referenciasPath/enfermeria/{$user->getSolicitud()->getId()}" . $filename;
        $pagoRepository = $entityManager->getRepository(Pago::class);

        try {
            if ($user->getStatus() == Alumno::STATUS_REJECTED_DOCUMENTACION) {
                $lastPago = $user->getLastPago();
                $referencia = $lastPago->getReferenciaBancaria();
            } else {
                $lastPago = $pagoRepository->getPagoPendienteByEscuelaEnfermeria($user->getId());
                if(!$lastPago){
                    throw new Exception('No se encontró un pago pendiente para el alumno con ID: ' . $user->getId());
                }
                $referencia = $lastPago->getReferenciaBancaria();
            }
        } catch (\Exception) {
            $referencia = $generadorRefenciaBancaria2025->generateNextReference();
            $pago = new Pago();
            $pago->setEscuelaEnfermeriaSolicitud($user);
            $pago->setReferenciaBancaria($referencia);
            $pago->setFechaCreacion(Carbon::now());
            if ($user->hasDiferencia()) {
                $pago->setMonto($user->getMontoDiferencia());
            } else {
                $pago->setMonto($user->getMonto());
            }
            $entityManager->persist($pago);
            $entityManager->flush();
        }

        $pdf->generateFromHtml(
            $this->renderView(
                'formatos/enfermeria/referencia.html.twig',
                [
                    'user' => $user,
                    'solicitud' => $user->getSolicitud(),
                    'referencia' => $referencia,
                ]
            ),
            $path,
            ['page-size' => 'Letter', 'encoding' => 'utf-8'],
            true
        );
        $filesize = filesize($path);
        $fileContent = file_get_contents($path);
        unlink($path);

        if (in_array($user->getStatus(), [Alumno::STATUS_INICIO, Alumno::STATUS_REJECTED])) {
            $user->setStatus(Alumno::STATUS_EN_ESPERA_PAGO);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $response = new Response($fileContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline;filename="' . $filename . '"');
        $response->headers->set('Content-length', $filesize);

        return $response;
    }

    #[Route('/carga-de-comprobante-de-pago', name: 'enfermeria-alumn.pago.carga_de_comprobante_de_pago')]
    public function cargaDeComprobanteDePago(
        MailerInterface $mailer,
        Environment $templating,
        Request $request,
        PagoRepositoryInterface $pagoRepository,
        NormalizerInterface $normalizer,
        UploaderComprobantePagoInterface $uploaderComprobantePago,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response {
        /** @var Alumno $alumno */
        $alumno = $this->getUser();
        $alumnoRepository = $this->em->getRepository(Alumno::class);

        try {
            if ($alumno->getStatus() === Alumno::STATUS_REJECTED_DOCUMENTACION) {
                $pago = $alumno->getLastPago();
            } else {
                $pago = $pagoRepository->getPagoPendienteByEscuelaEnfermeria($alumno->getId());
            }
        } catch (\Exception) {
            throw new NotFoundHttpException('No se encontró el pago pendiente');
        }

        $form = $this->createForm(ComprobantePagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Pago $pago */
            $pago = $form->getData();

            if ($alumno->getStatus() === Alumno::STATUS_REJECTED_DOCUMENTACION) {
                $pago->setValidado(null);
            }

            $cedula = $form->get('cedulaFile');

            if ($pago->isRequiereFactura() && (!$cedula || !$cedula->getData())) {
                $msgError = 'Para la emisión de la factura es necesaria la Cédula de Identificación Fiscal';
                $cedula->addError(new FormError($msgError));
            } else {
                if ($pago->isRequiereFactura() && $cedula) {
                    $file = $cedula->getData();
                    $directorioDestino = "$projectDir/public/uploads/alumno_enfermeria/{$alumno->getId()}/";
                    if (!file_exists($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }
                    $nombreArchivo = 'identificacion-fiscal.' . $file->guessExtension();
                    $file->move($directorioDestino, $nombreArchivo);
                    $alumno->setCedulaIdentificacion($nombreArchivo);
                    $entityManager->persist($alumno);
                }

                $pago->setFechaPago($pago->getFechaPagoRegistrada());
                $entityManager->persist($pago);
                $alumno->setStatus(Alumno::STATUS_ESPERA_VALIDACION);
                $entityManager->persist($alumno);
                $uploaderComprobantePago->update($pago);
                $entityManager->flush();

                return new JsonResponse([
                    'status' => true,
                    'data' => $normalizer->normalize($pago, 'json', [
                        'attributes' => ['id'],
                    ]),
                ]);
            }
        }

        return new JsonResponse([
            'status' => false,
            'message' => 'Error',
            'errors' => $this->getFormErrors($form),
        ], 400);
    }

    #[Route('/cedula-fiscal/{id}', name: 'enfermeria-alumno.cedula_fiscal')]
    public function downloadCedula(
        int $id,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): BinaryFileResponse {
        $alumno = $this->em->getRepository(Alumno::class)->find($id);

        if (!$alumno || !$alumno->getCedulaIdentificacion()) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        $rutaArchivo = "$projectDir/public/uploads/alumno_enfermeria/{$alumno->getId()}/" . $alumno->getCedulaIdentificacion();

        if (!file_exists($rutaArchivo)) {
            throw $this->createNotFoundException('El archivo no existe en el servidor. ' . $rutaArchivo);
        }

        $response = new BinaryFileResponse($rutaArchivo);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $alumno->getCedulaIdentificacion()
        );

        return $response;
    }

    #[Route('/{id}/factura', name: 'enfermeria-alumno.factura.download', methods: ['GET'])]
    public function downloadFactura(
        int $id,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): BinaryFileResponse {
        /** @var Factura $factura */
        $factura = $this->em->getRepository(Factura::class)->find($id);

        $rutaArchivo = "$projectDir/public/uploads/instituciones/facturas/" . $factura->getZip();

        if (!file_exists($rutaArchivo)) {
            throw $this->createNotFoundException('El archivo no existe en el servidor. ' . $rutaArchivo);
        }

        $response = new BinaryFileResponse($rutaArchivo);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $factura->getZip()
        );

        return $response;
    }

    private function serializerUser(): array
    {
        return $this->serializer->normalize($this->getUser(), 'json', [
            'attributes' => [
                'id', 'nombre', 'email', 'promedio', 'tipo', 'curp', 'monto', 'status',
                'statusFormatted',
                'solicitud' => [
                    'id', 'periodo', 'fechaInicioFormatted', 'fechaFinFormatted',
                    'unidad' => [
                        'id', 'nombre', 'nombreEnfermeria',
                    ],
                ],
                'lastPago' => [
                    'id', 'monto', 'fechaPagoFormatted', 'statusFormatted', 'observaciones',
                    'factura' => [
                        'id',
                    ],
                ],
            ],
        ]);
    }
}
