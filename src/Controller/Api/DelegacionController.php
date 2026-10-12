<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Entity\Delegacion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class DelegacionController extends DIEControllerController
{
    #[Route('/delegaciones', name: 'api.delegaciones.index', methods: ['GET'])]
    public function index(NormalizerInterface $normalizer): JsonResponse
    {
        $delegations = $this->em->getRepository(Delegacion::class)->findAll();

        return new JsonResponse([
            'data' => $normalizer->normalize($delegations, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }
}
