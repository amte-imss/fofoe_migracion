<?php

namespace App\Controller\Admin;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Entity\Unidad;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/enfermeria/solicitudes')]
class SolicitudEnfermeriaCrudController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'admin.enfermeria.solicitudes.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 20;
        $q       = $request->query->get('q', '');

        $qb = $this->em->getRepository(Solicitud::class)
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

        return $this->render('admin/enfermeria/solicitudes/index.html.twig', [
            'solicitudes' => $solicitudes,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'q'           => $q,
        ]);
    }

    #[Route('/{id}', name: 'admin.enfermeria.solicitud.show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            throw $this->createNotFoundException('Solicitud no encontrada.');
        }

        $unidades = $this->em->getRepository(Unidad::class)
            ->createQueryBuilder('u')
            ->where('u.esEnfermeria = true')
            ->orderBy('u.nombreEnfermeria', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/enfermeria/solicitudes/show.html.twig', [
            'solicitud' => $solicitud,
            'unidades'  => $unidades,
        ]);
    }

    #[Route('/{id}', name: 'admin.enfermeria.solicitud.update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            throw $this->createNotFoundException('Solicitud no encontrada.');
        }

        $solicitud->setPeriodo($request->request->get('periodo'));
        $solicitud->setFechaInicio(new \DateTime($request->request->get('fechaInicio')));
        $solicitud->setFechaFin(new \DateTime($request->request->get('fechaFin')));
        $solicitud->setIsTest((bool) $request->request->get('isTest', false));

        $unidadId = $request->request->get('unidad');
        if ($unidadId) {
            $unidad = $this->em->getRepository(Unidad::class)->find($unidadId);
            if ($unidad) {
                $solicitud->setUnidad($unidad);
            }
        }

        $this->em->flush();

        $this->addFlash('success', 'Solicitud actualizada correctamente.');
        return $this->redirectToRoute('admin.enfermeria.solicitud.show', ['id' => $id]);
    }

    #[Route('/{id}/alumno', name: 'admin.enfermeria.alumno.store', methods: ['POST'])]
    public function alumnoStore(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        if (!$solicitud) {
            return new JsonResponse(['error' => 'Solicitud no encontrada'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['nombre']) || empty($data['curp']) || empty($data['email']) || empty($data['tipo'])) {
            return new JsonResponse(['error' => 'Campos requeridos: nombre, curp, email, tipo'], 422);
        }

        $alumno = new Alumno();
        $alumno->setNombre($data['nombre']);
        $alumno->setCurp(strtoupper($data['curp']));
        $alumno->setEmail($data['email']);
        $alumno->setTipo($data['tipo']);
        $alumno->setStatus(Alumno::STATUS_INICIO);
        $alumno->setMonto(isset($data['monto']) ? (float) $data['monto'] : null);
        $alumno->setSolicitud($solicitud);

        $this->em->persist($alumno);
        $this->em->flush();

        return new JsonResponse([
            'id'              => $alumno->getId(),
            'idFormatted'     => $alumno->getIdFormatted(),
            'nombre'          => $alumno->getNombre(),
            'curp'            => $alumno->getCurp(),
            'email'           => $alumno->getEmail(),
            'tipo'            => $alumno->getTipo(),
            'monto'           => $alumno->getMonto(),
            'statusFormatted' => $alumno->getStatusFormatted(),
        ], 201);
    }

    #[Route('/{solicitudId}/alumno/{alumnoId}', name: 'admin.enfermeria.alumno.delete', methods: ['DELETE'])]
    public function alumnoDelete(int $solicitudId, int $alumnoId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $alumno = $this->em->getRepository(Alumno::class)->find($alumnoId);
        if (!$alumno || $alumno->getSolicitud()?->getId() !== $solicitudId) {
            return new JsonResponse(['error' => 'No encontrado'], 404);
        }

        $this->em->remove($alumno);
        $this->em->flush();

        return new JsonResponse(['ok' => true]);
    }
}
