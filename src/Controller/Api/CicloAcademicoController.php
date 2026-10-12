<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Repository\CicloAcademicoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class CicloAcademicoController extends DIEControllerController
{
    #[Route('/ciclos-academicos', name: 'api.ciclo-academico.index', methods: ['GET'])]
    public function index(CicloAcademicoRepository $cicloAcademicoRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $academicCycles = $cicloAcademicoRepository->findBy([], ['orderSort' => 'ASC']);

        return new JsonResponse([
            'data' => $normalizer->normalize($academicCycles, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }
}
