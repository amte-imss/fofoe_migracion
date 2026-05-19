<?php

namespace App\Controller\Enfermeria;

use App\Entity\ConfiguracionGlobal;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Entity\Unidad;
use App\Repository\Enfermeria\SolicitudRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enfermeria')]
class EnfermeriaController extends AbstractController
{
    #[Route('/', name: 'enfermeria.index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        // Bloquear admins (ROLE_SUPER hereda ROLE_IE, pero no deben entrar aquí)
        if ($this->isGranted('ROLE_ADM_FOFOE')) {
            return $this->redirectToRoute('app_dashboard');
        }
        $user = $this->getUser();

        $escuelaId   = null;
        $ooadId      = null;
        $directRoles = $user->getRoles();
        $asCame      = in_array('ROLE_CAME_MINUS', $directRoles) || in_array('ROLE_JDES_MINUS', $directRoles)
                    || in_array('ROLE_CAME', $directRoles) || in_array('ROLE_JDES', $directRoles);

        // Si es usuario CAME/JDES, usar la OOAD/Unidad guardada en sesión
        if ($asCame) {
            $session      = $request->getSession();
            $delSesion    = $session->get('user_delegacion');
            $unidSesion   = $session->get('user_unidad');
            $isDelActiva  = !empty($delSesion) || empty($unidSesion);

            if ($isDelActiva && method_exists($user, 'getDelegaciones')) {
                // Usar delegación de sesión o la primera del usuario
                $delegaciones = $user->getDelegaciones();
                if (!empty($delSesion)) {
                    foreach ($delegaciones as $d) {
                        if ((string) $d->getId() === (string) $delSesion) {
                            $ooadId = $d->getId();
                            break;
                        }
                    }
                } elseif (!$delegaciones->isEmpty()) {
                    $ooadId = $delegaciones->first()->getId();
                }
            } elseif (!empty($unidSesion) && method_exists($user, 'getUnidades')) {
                foreach ($user->getUnidades() as $u) {
                    if ((string) $u->getId() === (string) $unidSesion) {
                        $escuelaId = $u->getId();
                        break;
                    }
                }
            }
        } else {
            // Usuario IE: usar su primera unidad
            if (method_exists($user, 'getUnidades') && count($user->getUnidades()) > 0) {
                $escuelaId = $user->getUnidades()[0]->getId();
            }
            if (method_exists($user, 'getDelegacion') && $user->getDelegacion()) {
                $ooadId = $user->getDelegacion()->getId();
            }
        }

        return $this->render('enfermeria/index.html.twig', [
            'escuelaId' => $escuelaId ?? '',
            'asCame'    => $asCame,
            'ooad'      => $ooadId ?? '',
        ]);
    }

    #[Route('/solicitud/{id}', name: 'enfermeria.solicitud.show', methods: ['GET'])]
    public function show(Solicitud $solicitud): Response
    {
        $alumnos = [];
        foreach ($solicitud->getAlumnos() as $alumno) {
            $alumnos[] = [
                'id'             => $alumno->getId(),
                'nombre'         => $alumno->getNombre(),
                'curp'           => $alumno->getCurp(),
                'email'          => $alumno->getEmail(),
                'tipo'           => $alumno->getTipo(),
                'monto'          => $alumno->getMonto(),
                'statusFormatted' => $alumno->getStatusFormatted(),
            ];
        }

        $solicitudData = [
            'id'                  => $solicitud->getId(),
            'periodo'             => $solicitud->getPeriodo(),
            'periodoFormatted'    => $solicitud->getPeriodoFormatted(),
            'fechaInicioFormatted' => $solicitud->getFechaInicioFormatted(),
            'fechaFinFormatted'   => $solicitud->getFechaFinFormatted() ?? '',
            'alumnos'             => $alumnos,
            'unidad'              => $solicitud->getUnidad() ? [
                'id'              => $solicitud->getUnidad()->getId(),
                'nombre'          => $solicitud->getUnidad()->getNombre(),
                'nombreEnfermeria' => $solicitud->getUnidad()->getNombreEnfermeria(),
            ] : null,
        ];

        return $this->render('enfermeria/show.html.twig', [
            'solicitud' => $solicitudData,
            'qr'        => '',
        ]);
    }

    #[Route('/solicitud/nueva', name: 'enfermeria.solicitud.create', methods: ['GET'])]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isGranted('ROLE_ADM_FOFOE')) {
            return $this->redirectToRoute('app_dashboard');
        }
        $user = $this->getUser();

        $escuelaId   = null;
        $ooadId      = null;
        $directRoles = $user->getRoles();
        $asCame      = in_array('ROLE_CAME_MINUS', $directRoles) || in_array('ROLE_JDES_MINUS', $directRoles)
                    || in_array('ROLE_CAME', $directRoles) || in_array('ROLE_JDES', $directRoles);

        if ($asCame) {
            $session     = $request->getSession();
            $delSesion   = $session->get('user_delegacion');
            $unidSesion  = $session->get('user_unidad');
            $isDelActiva = !empty($delSesion) || empty($unidSesion);
            if ($isDelActiva && method_exists($user, 'getDelegaciones')) {
                $delegaciones = $user->getDelegaciones();
                if (!empty($delSesion)) {
                    foreach ($delegaciones as $d) {
                        if ((string) $d->getId() === (string) $delSesion) { $ooadId = $d->getId(); break; }
                    }
                } elseif (!$delegaciones->isEmpty()) {
                    $ooadId = $delegaciones->first()->getId();
                }
            } elseif (!empty($unidSesion) && method_exists($user, 'getUnidades')) {
                foreach ($user->getUnidades() as $u) {
                    if ((string) $u->getId() === (string) $unidSesion) { $escuelaId = $u->getId(); break; }
                }
            }
        } else {
            if (method_exists($user, 'getUnidades') && count($user->getUnidades()) > 0) {
                $escuelaId = $user->getUnidades()[0]->getId();
            }
            if (method_exists($user, 'getDelegacion') && $user->getDelegacion()) {
                $ooadId = $user->getDelegacion()->getId();
            }
        }

        return $this->render('enfermeria/create.html.twig', [
            'escuelaId' => $escuelaId ?? '',
            'asCame'    => $asCame,
            'ooad'      => $ooadId ?? '',
        ]);
    }

    #[Route('/solicitud/{id}/edit', name: 'enfermeria.solicitud.edit', methods: ['GET', 'POST'])]
    public function edit(Solicitud $solicitud, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request;

            $solicitud->setPeriodo($data->get('periodo'));
            $solicitud->setFechaInicio(new \DateTime($data->get('fechaInicio')));
            $solicitud->setFechaFin(new \DateTime($data->get('fechaFin')));
            $solicitud->setIsTest((bool) $data->get('isTest', false));

            // Cambio de unidad
            $unidadId = $data->get('unidadId');
            if ($unidadId) {
                $unidad = $em->getRepository(Unidad::class)->find($unidadId);
                if ($unidad) {
                    $solicitud->setUnidad($unidad);
                }
            }

            // Eliminación de alumnos seleccionados
            $alumnosEliminar = $data->all('alumnosEliminar') ?? [];
            foreach ($alumnosEliminar as $alumnoId) {
                $alumno = $em->getRepository(Alumno::class)->find($alumnoId);
                if ($alumno && $alumno->getSolicitud() === $solicitud) {
                    $solicitud->getAlumnos()->removeElement($alumno);
                    $alumno->setSolicitud(null);
                    $em->remove($alumno);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Solicitud actualizada correctamente.');
            return $this->redirectToRoute('enfermeria.solicitud.show', ['id' => $solicitud->getId()]);
        }

        // Buscar unidades de enfermería para el select
        $unidades = $em->getRepository(Unidad::class)->findBy(
            ['esEnfermeria' => true],
            ['nombre' => 'ASC']
        );

        return $this->render('enfermeria/edit.html.twig', [
            'solicitud' => $solicitud,
            'unidades'  => $unidades,
        ]);
    }

    #[Route('/solicitud/{id}/export.csv', name: 'enfermeria.solicitud.export', methods: ['GET'])]
    public function exportCsv(Solicitud $solicitud): Response
    {
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

        return new Response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alumnos_lote_' . $solicitud->getId() . '.csv"',
        ]);
    }

    #[Route('/api/solicitudes', name: 'enfermeria.api.solicitudes', methods: ['GET'])]
    public function apiSolicitudes(SolicitudRepository $solicitudRepository, Request $request): JsonResponse
    {
        $year = $request->query->get('year');

        $user = $this->getUser();
        // Determinar scope: por escuela o por delegación (CAME)
        // Por ahora devuelve paginado general (extender con lógica de usuario)
        $data = $solicitudRepository->findAllPaginated(1, 50);

        $result = [];
        foreach ($data as $row) {
            $solicitud = is_array($row) ? $row[0] : $row;
            $totalAlumnos = is_array($row) ? ($row['totalAlumnos'] ?? 0) : 0;
            $result[] = [
                'id'               => $solicitud->getId(),
                'periodo'          => $solicitud->getPeriodo(),
                'periodoFormatted' => $solicitud->getPeriodoFormatted(),
                'fechaInicio'      => $solicitud->getFechaInicioFormatted(),
                'fechaFin'         => $solicitud->getFechaFinFormatted(),
                'createdAt'        => $solicitud->getCreatedAtFormatted(),
                'totalAlumnos'     => $totalAlumnos,
                'unidad'           => $solicitud->getUnidad() ? [
                    'id'     => $solicitud->getUnidad()->getId(),
                    'nombre' => $solicitud->getUnidad()->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['data' => $result]);
    }
}
