<?php

namespace App\Controller\Enfermeria;

use App\Entity\ConfiguracionGlobal;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Form\Enfermeria\AlumnoType;
use App\Form\Enfermeria\SolicitudType;
use App\Repository\Enfermeria\SolicitudRepository;
use App\Service\GeneradorRefenciaBancaria2025;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/enfermeria/solicitud')]
class SolicitudController extends AbstractController
{
    /**
     * GET /enfermeria/solicitud/api
     * Lista de solicitudes (JSON) filtradas por año para el panel de la escuela.
     */
    #[Route('/api', name: 'enfermeria.solicitud.api', methods: ['GET'])]
    public function apiIndex(
        SolicitudRepository    $solicitudRepository,
        EntityManagerInterface $em,
        Request                $request
    ): JsonResponse {
        $user    = $this->getUser();
        $query   = $request->query->get('query');

        /** @var ConfiguracionGlobal|null $config */
        $config = $em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        $asCame = $config && $config->getValor();

        // Determinar escuela o delegación según rol y sesión
        if ($asCame && ($this->isGranted('ROLE_CAME_MINUS') || $this->isGranted('ROLE_JDES_MINUS'))) {
            $session     = $request->getSession();
            $delSesion   = $session->get('user_delegacion');
            $unidSesion  = $session->get('user_unidad');
            $isDelActiva = !empty($delSesion) || empty($unidSesion);

            $delegacionId = null;
            if ($isDelActiva && method_exists($user, 'getDelegaciones')) {
                $delegaciones = $user->getDelegaciones();
                if (!empty($delSesion)) {
                    foreach ($delegaciones as $d) {
                        if ((string) $d->getId() === (string) $delSesion) { $delegacionId = $d->getId(); break; }
                    }
                } elseif (!$delegaciones->isEmpty()) {
                    $delegacionId = $delegaciones->first()->getId();
                }
            }
            $data = $solicitudRepository->findByYearAndOOAD($query, $delegacionId);
        } else {
            $escuelaId = null;
            if (method_exists($user, 'getUnidades') && count($user->getUnidades()) > 0) {
                $escuelaId = $user->getUnidades()[0]->getId();
            }
            $data = $solicitudRepository->findByYear($query, $escuelaId);
        }

        // El React espera: data = [ [ {solicitud}, totalAlumnos ], ... ]
        // donde item[0] = solicitud, item.totalAlumnos = count
        $result = [];
        foreach ($data as $row) {
            /** @var Solicitud $solicitud */
            $solicitud    = is_array($row) ? $row[0] : $row;
            $totalAlumnos = is_array($row) ? (int)($row['totalAlumnos'] ?? 0) : 0;

            $result[] = [
                [
                    'id'                   => $solicitud->getId(),
                    'periodo'              => $solicitud->getPeriodo(),
                    'periodoFormatted'     => $solicitud->getPeriodoFormatted(),
                    'createdAtFormatted'   => $solicitud->getCreatedAtFormatted(),
                    'fechaInicioFormatted' => $solicitud->getFechaInicioFormatted(),
                    'fechaFinFormatted'    => $solicitud->getFechaFinFormatted(),
                    'unidad' => $solicitud->getUnidad() ? [
                        'id'               => $solicitud->getUnidad()->getId(),
                        'nombre'           => $solicitud->getUnidad()->getNombre(),
                        'nombreEnfermeria' => method_exists($solicitud->getUnidad(), 'getNombreEnfermeria')
                            ? $solicitud->getUnidad()->getNombreEnfermeria() : $solicitud->getUnidad()->getNombre(),
                    ] : null,
                ],
                'totalAlumnos' => $totalAlumnos,
            ];
        }

        return new JsonResponse([
            'data' => $result,
        ]);
    }

    /**
     * GET /enfermeria/solicitud/create
     * Vista de creación de una nueva solicitud (escuela).
     */
    #[Route('/create', name: 'enfermeria.solicitud.create.view', methods: ['GET'])]
    public function createView(EntityManagerInterface $em): Response
    {
        $config = $em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        $user = $this->getUser();
        $escuelaId = null;
        $ooadId    = null;

        if (method_exists($user, 'getUnidades') && count($user->getUnidades()) > 0) {
            $escuelaId = $user->getUnidades()[0]->getId();
        }
        if (method_exists($user, 'getDelegaciones') && !$user->getDelegaciones()->isEmpty()) {
            $ooadId = $user->getDelegaciones()->first()->getId();
        } elseif (method_exists($user, 'getDelegacionInstitucion') && $user->getDelegacionInstitucion()) {
            $ooadId = $user->getDelegacionInstitucion()->getId();
        }

        return $this->render('enfermeria/create.html.twig', [
            'escuelaId' => $escuelaId ?? '',
            'asCame'    => $config ? (int) $config->getValor() : 0,
            'ooad'      => $ooadId ?? '',
        ]);
    }

    /**
     * POST /enfermeria/solicitud/api
     * Guarda una nueva solicitud (lote).
     */
    #[Route('/api', name: 'enfermeria.solicitud.store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $form = $this->createForm(SolicitudType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Solicitud $solicitud */
            $solicitud = $form->getData();
            $em->persist($solicitud);
            $em->flush();

            return new JsonResponse([
                'status' => true,
                'data'   => ['id' => $solicitud->getId()],
            ]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error al crear la solicitud.',
            'errors'  => $this->getFormErrors($form),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * POST /enfermeria/solicitud/alumno/api
     * Registra un alumno en un lote y le envía correo de bienvenida.
     */
    #[Route('/alumno/api', name: 'enfermeria.solicitud.alumno.store', methods: ['POST'])]
    public function storeAlumno(
        Request                $request,
        EntityManagerInterface $em,
        MailerInterface        $mailer,
        \Psr\Log\LoggerInterface $logger
    ): JsonResponse {
        $form = $this->createForm(AlumnoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Alumno $alumno */
            $alumno = $form->getData();

            // Asignar monto según tipo de alumno desde ConfiguracionGlobal
            $monto = $em->getRepository(ConfiguracionGlobal::class)
                ->findMontoEF($alumno->getTipo());
            if ($monto) {
                $alumno->setMonto((float) $monto->getValor());
            }

            $em->persist($alumno);
            $em->flush();

            // Recargar para tener relaciones completas
            $alumno = $em->getRepository(Alumno::class)->find($alumno->getId());

            try {
                $this->sendBienvenidaEmail($mailer, $alumno);
            } catch (\Throwable $e) {
                $logger->error('[MAIL ERROR] ' . $e->getMessage(), ['exception' => $e]);
            }

            return new JsonResponse([
                'status' => true,
                'data'   => ['id' => $alumno->getId()],
            ]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error al registrar el alumno.',
            'errors'  => $this->getFormErrors($form),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * GET /enfermeria/solicitud/{id}
     * Detalle de una solicitud (QR + lista de alumnos).
     */
    #[Route('/{id}', name: 'enfermeria.solicitud.show', methods: ['GET'])]
    public function show(
        int                    $id,
        EntityManagerInterface $em
    ): Response {
        // Admin: redirigir a la vista de edición admin
        if ($this->isGranted('ROLE_ADM_FOFOE') || $this->isGranted('ROLE_SUPER')) {
            return $this->redirectToRoute('admin.enfermeria.solicitud.show', ['id' => $id]);
        }

        $solicitud = $em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            throw $this->createNotFoundException('Solicitud no encontrada.');
        }

        // Generar URL para el login del alumno
        $loginUrl = $this->generateUrl(
            'enfermeria_alumno.login',
            ['solicitudId' => $solicitud->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Preparar datos de alumnos
        $alumnos = [];
        foreach ($solicitud->getAlumnos() as $alumno) {
            $alumnos[] = [
                'id'              => $alumno->getId(),
                'nombre'          => $alumno->getNombre(),
                'email'           => $alumno->getEmail(),
                'curp'            => $alumno->getCurp(),
                'tipo'            => $alumno->getTipo(),
                'monto'           => $alumno->getMonto(),
                'status'          => $alumno->getStatus(),
                'statusFormatted' => $alumno->getStatusFormatted(),
                'promedio'        => $alumno->getPromedio(),
            ];
        }

        $solicitudData = [
            'id'               => $solicitud->getId(),
            'periodo'          => $solicitud->getPeriodo(),
            'periodoFormatted' => $solicitud->getPeriodoFormatted(),
            'fechaInicioFormatted' => $solicitud->getFechaInicioFormatted(),
            'fechaFinFormatted'    => $solicitud->getFechaFinFormatted(),
            'alumnos'              => $alumnos,
        ];

        return $this->render('enfermeria/show.html.twig', [
            'solicitud'   => $solicitudData,
            'qr'          => '',
            'loginUrl'    => $loginUrl,
            'solicitudId' => $solicitud->getId(),
        ]);
    }

    /**
     * GET /enfermeria/solicitud/{id}/export
     * Exportar alumnos del lote a CSV.
     */
    #[Route('/{id}/export', name: 'enfermeria.solicitud.export', methods: ['GET'])]
    public function exportCsv(int $id, EntityManagerInterface $em): Response
    {
        $solicitud = $em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            throw $this->createNotFoundException('Solicitud no encontrada.');
        }

        $content = "CURP,NOMBRE,EMAIL,TIPO ALUMNO,MONTO,ESTADO PROCESO\n";
        foreach ($solicitud->getAlumnos() as $alumno) {
            $content .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $alumno->getCurp(),
                $alumno->getNombre(),
                $alumno->getEmail(),
                $alumno->getTipo(),
                $alumno->getMonto(),
                $alumno->getStatusFormatted()
            );
        }

        return new Response($content, Response::HTTP_OK, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alumnos_lote_' . $id . '.csv"',
        ]);
    }

    // -------------------------------------------------------------------------

    private function sendBienvenidaEmail(MailerInterface $mailer, Alumno $alumno): void
    {
        $from = $this->getParameter('mailer_sender');

        $email = (new TemplatedEmail())
            ->from(new Address($from))
            ->to(new Address($alumno->getEmail()))
            ->subject('Sistema de Administración del FOFOE — Credenciales de acceso')
            ->htmlTemplate('emails/enfermeria/alumno_bienvenida.html.twig')
            ->context([
                'alumno'    => $alumno,
                'solicitud' => $alumno->getSolicitud(),
            ]);

        $mailer->send($email);

        // Copia interna
        $emailCopia = (new TemplatedEmail())
            ->from(new Address($from))
            ->to('zurgcom@gmail.com', 'eliarteaga1977@gmail.com')
            ->subject('Sistema de Administración del FOFOE — Credenciales de acceso (copia)')
            ->htmlTemplate('emails/enfermeria/alumno_bienvenida.html.twig')
            ->context([
                'alumno'    => $alumno,
                'solicitud' => $alumno->getSolicitud(),
            ]);

        $mailer->send($emailCopia);
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
