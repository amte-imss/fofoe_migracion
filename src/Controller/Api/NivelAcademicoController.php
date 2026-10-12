<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Entity\NivelAcademico;
use App\Form\Type\Catalogos\NivelAcademicoType;
use App\Repository\NivelAcademicoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class NivelAcademicoController extends DIEControllerController
{
    #[Route('/nivel-academico', name: 'api.nivel-academico.index', methods: ['GET'])]
    public function index(NivelAcademicoRepository $nivelAcademicoRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $academicLevels = $nivelAcademicoRepository->findBy([], ['orderSort' => 'ASC', 'nombre' => 'ASC']);

        return new JsonResponse([
            'data' => $normalizer->normalize($academicLevels, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }

    #[Route('/nivel-academico', name: 'api.nivel-academico.store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(NivelAcademicoType::class);
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

        /** @var NivelAcademico $academicLevel */
        $academicLevel = $form->getData();
        $academicLevel->setOrderSort(0);

        $this->em->persist($academicLevel);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Creado con éxito',
            'data' => [
                'id' => $academicLevel->getId(),
                'nombre' => $academicLevel->getNombre(),
            ],
        ]);
    }
}
