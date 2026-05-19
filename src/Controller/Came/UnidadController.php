<?php

namespace App\Controller\Came;

use App\Entity\Unidad;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UnidadController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/came/api/unidad_enfermeria/{ooad}', name: 'came.unidad-enfermeria.index', methods: ['GET'])]
    public function unidadesEnfermeria(Request $request, int $ooad): JsonResponse
    {
        $unidades = $this->em->getRepository(Unidad::class)->findBy([
            'esEnfermeria' => true,
            'delegacionId' => $ooad,
        ]);

        $data = array_map(fn(Unidad $u) => [
            'id'              => $u->getId(),
            'nombre'          => $u->getNombre(),
            'claveUnidad'     => $u->getClaveUnidad(),
            'nombreEnfermeria' => $u->getNombreEnfermeria(),
        ], $unidades);

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/api/unidad_enfermeria', name: 'api.unidad-enfermeria.all', methods: ['GET'])]
    public function todasUnidadesEnfermeria(): JsonResponse
    {
        $unidades = $this->em->getRepository(Unidad::class)->findBy(
            ['esEnfermeria' => true],
            ['nombreEnfermeria' => 'ASC']
        );

        $data = array_map(fn(Unidad $u) => [
            'id'               => $u->getId(),
            'nombre'           => $u->getNombre(),
            'claveUnidad'      => $u->getClaveUnidad(),
            'nombreEnfermeria' => $u->getNombreEnfermeria(),
        ], $unidades);

        return new JsonResponse(['data' => $data]);
    }
}
