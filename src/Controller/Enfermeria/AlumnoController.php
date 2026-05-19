<?php

namespace App\Controller\Enfermeria;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Unidad;
use App\Repository\Enfermeria\AlumnoRepository;
use App\Repository\Enfermeria\SolicitudRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enfermeria/alumnos')]
class AlumnoController extends AbstractController
{
    #[Route('/', name: 'enfermeria.alumno.index', methods: ['GET'])]
    public function index(
        AlumnoRepository   $alumnoRepo,
        SolicitudRepository $solicitudRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $status      = $request->query->get('status', '');
        $solicitudId = (int) $request->query->get('solicitudId', 0) ?: null;
        $unidadId    = (int) $request->query->get('unidadId', 0) ?: null;
        $year        = (int) $request->query->get('year', date('Y')) ?: null;
        $page        = max(1, (int) $request->query->get('page', 1));
        $perPage     = 25;

        $data      = $alumnoRepo->findByFilters($status, $solicitudId, $unidadId, $year, $page, $perPage);
        $conteos   = $alumnoRepo->countByStatus();
        $unidades  = $em->getRepository(Unidad::class)->findBy(['esEnfermeria' => true], ['nombre' => 'ASC']);

        return $this->render('enfermeria/alumno/index.html.twig', [
            'alumnos'     => $data['results'],
            'total'       => $data['total'],
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int) ceil($data['total'] / $perPage),
            'conteos'     => $conteos,
            'unidades'    => $unidades,
            'filters'     => [
                'status'      => $status,
                'solicitudId' => $solicitudId,
                'unidadId'    => $unidadId,
                'year'        => $year,
            ],
        ]);
    }

    #[Route('/{id}', name: 'enfermeria.alumno.show', methods: ['GET'])]
    public function show(Alumno $alumno): Response
    {
        return $this->render('enfermeria/alumno/show.html.twig', [
            'alumno' => $alumno,
        ]);
    }

    #[Route('/{id}/estado', name: 'enfermeria.alumno.updateStatus', methods: ['POST'])]
    public function updateStatus(Alumno $alumno, Request $request, EntityManagerInterface $em): Response
    {
        $nuevoStatus  = $request->request->get('status');
        $observaciones = $request->request->get('observaciones', '');

        $statusValidos = [
            Alumno::STATUS_INICIO,
            Alumno::STATUS_EN_ESPERA_PAGO,
            Alumno::STATUS_ESPERA_VALIDACION,
            Alumno::STATUS_VALIDATED,
            Alumno::STATUS_REJECTED,
            Alumno::STATUS_REJECTED_DOCUMENTACION,
            Alumno::STATUS_INVOICE_PENDING,
        ];

        if (!in_array($nuevoStatus, $statusValidos, true)) {
            $this->addFlash('error', 'Estado no válido.');
            return $this->redirectToRoute('enfermeria.alumno.show', ['id' => $alumno->getId()]);
        }

        $alumno->setStatus($nuevoStatus);

        // Si hay un pago pendiente, actualizar observaciones
        $lastPago = $alumno->getLastPago();
        if ($lastPago && $observaciones) {
            $lastPago->setObservaciones($observaciones);
        }

        // Marcar pago como validado/rechazado según nuevo estado
        if ($lastPago) {
            if ($nuevoStatus === Alumno::STATUS_VALIDATED || $nuevoStatus === Alumno::STATUS_INVOICE_PENDING) {
                $lastPago->setValidado(true);
                $alumno->setHasDiferencia(false);
                $alumno->setMontoDiferencia(0);
            } elseif (in_array($nuevoStatus, [Alumno::STATUS_REJECTED, Alumno::STATUS_REJECTED_DOCUMENTACION], true)) {
                $lastPago->setValidado(false);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Estado actualizado correctamente.');
        return $this->redirectToRoute('enfermeria.alumno.show', ['id' => $alumno->getId()]);
    }

    #[Route('/{id}/cedula', name: 'enfermeria.alumno.downloadCedula', methods: ['GET'])]
    public function downloadCedula(Alumno $alumno): Response
    {
        if (!$alumno->getCedulaIdentificacion()) {
            throw $this->createNotFoundException('No hay cédula de identificación fiscal registrada.');
        }

        $ruta = $this->getParameter('kernel.project_dir')
            . '/public/uploads/alumno_enfermeria/' . $alumno->getId() . '/'
            . $alumno->getCedulaIdentificacion();

        if (!file_exists($ruta)) {
            // Buscar también en la ruta legacy
            $rutaLegacy = $this->getParameter('kernel.project_dir')
                . '/../uploads/alumno_enfermeria/' . $alumno->getId() . '/'
                . $alumno->getCedulaIdentificacion();
            if (file_exists($rutaLegacy)) {
                $ruta = $rutaLegacy;
            } else {
                throw $this->createNotFoundException('El archivo no existe en el servidor.');
            }
        }

        $response = new BinaryFileResponse($ruta);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $alumno->getCedulaIdentificacion());
        return $response;
    }
}
