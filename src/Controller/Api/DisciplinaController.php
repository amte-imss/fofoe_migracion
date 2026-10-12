<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Entity\Disciplina;
use App\Form\Type\Catalogos\DisciplinaType;
use App\Repository\DisciplinaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class DisciplinaController extends DIEControllerController
{
    #[Route('/disciplinas', name: 'api.disciplinas.index', methods: ['GET'])]
    public function index(DisciplinaRepository $disciplinaRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $disciplines = $disciplinaRepository->findBy(
            ['activa' => true],
            ['orderSort' => 'ASC', 'nombre' => 'ASC'],
        );

        return new JsonResponse([
            'data' => $normalizer->normalize($disciplines, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }

    #[Route('/disciplinas', name: 'api.disciplinas.store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(DisciplinaType::class);
        $form->handleRequest($request);

        $isSubmitted = $form->isSubmitted();
        $isValid = $isSubmitted && $form->isValid();

        if (!$isValid) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error',
                'submited' => $isSubmitted ? 1 : 0,
                'valid' => 0,
                'errors' => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Disciplina $discipline */
        $discipline = $form->getData();
        $discipline->setOrderSort(0);

        $this->em->persist($discipline);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Creado con éxito',
            'data' => [
                'id' => $discipline->getId(),
                'nombre' => $discipline->getNombre(),
            ],
        ]);
    }
}
