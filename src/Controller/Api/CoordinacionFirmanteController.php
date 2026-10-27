<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Repository\CoordinacionFirmanteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class CoordinacionFirmanteController extends DIEControllerController
{
    #[Route('/coordinacion-firmante', name: 'api.coordinacion-firmante.index', methods: ['GET'])]
    public function index(CoordinacionFirmanteRepository $coordinacionFirmanteRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $coordinations = $coordinacionFirmanteRepository->findBy(
            ['activa' => true],
            ['orderSort' => 'ASC', 'nombre' => 'ASC'],
        );

        return new JsonResponse([
            'data' => $normalizer->normalize($coordinations, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }
}
