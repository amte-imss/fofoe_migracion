<?php

namespace App\Controller\Fofoe\Enfermeria;

use App\Entity\Enfermeria\Solicitud;
use App\Entity\Pago;
use App\Repository\PagoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador FOFOE — Vista de resumen de solicitudes/pagos de Enfermería.
 */
#[Route('/fofoe/enfermeria')]
class SolicitudController extends AbstractController
{
    /**
     * GET /fofoe/enfermeria/solicitud
     * Listado JSON de pagos de Escuelas de Enfermería por estado.
     */
    #[Route('/solicitud', name: 'fofoe.enfermeria.solicitud.api', methods: ['GET'])]
    public function apiIndex(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $status = $request->query->get('estado', '');

        /** @var PagoRepository $pagoRepo */
        $pagoRepo = $em->getRepository(Pago::class);
        $pagos    = $pagoRepo->findEscuelaEnfermeriaByStatus($status);

        $result = [];
        foreach ($pagos as $pago) {
            try {
                $alumno    = $pago->getEscuelaEnfermeriaSolicitud();
                $solicitud = $alumno ? $alumno->getSolicitud() : null;

                $result[] = [
                    'id'                => $pago->getId(),
                    'fechaPagoFormatted' => method_exists($pago, 'getFechaPagoFormatted') ? $pago->getFechaPagoFormatted() : '',
                    'statusFormatted'   => method_exists($pago, 'getStatusFormatted') ? $pago->getStatusFormatted() : '',
                    'escuelaEnfermeriaSolicitud' => $alumno ? [
                        'nombre'          => $alumno->getNombre(),
                        'statusFormatted' => $alumno->getStatusFormatted(),
                        'solicitud'       => $solicitud ? [
                            'id'                   => $solicitud->getId(),
                            'fechaInicioFormatted'  => $solicitud->getFechaInicioFormatted(),
                            'fechaFinFormatted'     => $solicitud->getFechaFinFormatted(),
                            'unidad'               => $solicitud->getUnidad() ? [
                                'nombre'          => $solicitud->getUnidad()->getNombre(),
                                'id'              => $solicitud->getUnidad()->getId(),
                                'nombreEnfermeria' => method_exists($solicitud->getUnidad(), 'getNombreEnfermeria')
                                    ? $solicitud->getUnidad()->getNombreEnfermeria() : '',
                            ] : null,
                        ] : null,
                    ] : null,
                ];
            } catch (\Exception $e) {
                // Registro huérfano — omitir
                continue;
            }
        }

        return new JsonResponse([
            'meta' => ['total' => count($result)],
            'data' => $result,
        ]);
    }

    /**
     * GET /fofoe/enfermeria/
     * Vista principal FOFOE — resumen de pagos de enfermería.
     */
    #[Route('/', name: 'fofoe.enfermeria.index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 20;
        $q       = $request->query->get('q', '');

        $qb = $em->getRepository(Solicitud::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.unidad', 'u')
            ->orderBy('s.id', 'DESC');

        if ($q) {
            $qb->andWhere('u.nombreEnfermeria LIKE :q OR u.nombre LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $total       = (clone $qb)->select('COUNT(s.id)')->resetDQLPart('orderBy')->getQuery()->getSingleScalarResult();
        $solicitudes = $qb->setFirstResult(($page - 1) * $perPage)
                          ->setMaxResults($perPage)
                          ->getQuery()
                          ->getResult();

        return $this->render('enfermeria/fofoe/index.html.twig', [
            'solicitudes' => $solicitudes,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'q'           => $q,
        ]);
    }

    /**
     * GET /fofoe/enfermeria/solicitud/{id}
     * Detalle de un pago específico para revisión FOFOE.
     */
    #[Route('/solicitud/{id}', name: 'fofoe.enfermeria.solicitud.show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        /** @var Pago $pago */
        $pago = $em->getRepository(Pago::class)->find($id);
        if (!$pago) {
            throw $this->createNotFoundException('Pago no encontrado.');
        }

        $alumno    = $pago->getEscuelaEnfermeriaSolicitud();
        $solicitud = $alumno ? $alumno->getSolicitud() : null;
        $factura   = $pago->getFactura();

        $pagoData = [
            'id'                => $pago->getId(),
            'monto'             => $pago->getMonto(),
            'montoRegistrado'   => $pago->getMontoRegistrado(),
            'referenciaBancaria' => $pago->getReferenciaBancaria(),
            'observaciones'     => $pago->getObservaciones(),
            'validado'          => $pago->getValidado(),
            'requiereFactura'   => $pago->isRequiereFactura(),
            'fechaPagoFormatted' => method_exists($pago, 'getFechaPagoFormatted') ? $pago->getFechaPagoFormatted() : '',
            'statusFormatted'   => method_exists($pago, 'getStatusFormatted') ? $pago->getStatusFormatted() : '',
            'factura'           => $factura ? [
                'id'                      => $factura->getId(),
                'folio'                   => $factura->getFolio(),
                'fechaFacturacionFormatted' => method_exists($factura, 'getFechaFacturacionFormatted')
                    ? $factura->getFechaFacturacionFormatted() : '',
                'monto' => $factura->getMonto(),
            ] : null,
            'escuelaEnfermeriaSolicitud' => $alumno ? [
                'id'              => $alumno->getId(),
                'nombre'          => $alumno->getNombre(),
                'email'           => $alumno->getEmail(),
                'idFormatted'     => $alumno->getIdFormatted(),
                'status'          => $alumno->getStatus(),
                'statusFormatted' => $alumno->getStatusFormatted(),
                'solicitud'       => $solicitud ? [
                    'id'                   => $solicitud->getId(),
                    'fechaInicioFormatted'  => $solicitud->getFechaInicioFormatted(),
                    'fechaFinFormatted'     => $solicitud->getFechaFinFormatted(),
                    'createdAtFormatted'    => $solicitud->getCreatedAtFormatted(),
                    'unidad'               => $solicitud->getUnidad() ? [
                        'nombre'          => $solicitud->getUnidad()->getNombre(),
                        'id'              => $solicitud->getUnidad()->getId(),
                        'nombreEnfermeria' => method_exists($solicitud->getUnidad(), 'getNombreEnfermeria')
                            ? $solicitud->getUnidad()->getNombreEnfermeria() : '',
                    ] : null,
                ] : null,
            ] : null,
        ];

        return $this->render('enfermeria/fofoe/show.html.twig', [
            'pago' => $pagoData,
        ]);
    }
}
