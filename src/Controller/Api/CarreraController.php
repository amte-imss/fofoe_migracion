<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use App\Entity\Carrera;
use App\Form\Type\Catalogos\CarreraType;
use App\Repository\CarreraRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api')]
class CarreraController extends DIEControllerController
{
    #[Route('/carreras', name: 'api.carreras.index', methods: ['GET'])]
    public function index(
        Request $request,
        CarreraRepository $carreraRepository,
        NormalizerInterface $normalizer,
    ): JsonResponse {
        $filters = ['activo' => true];

        $academicLevel = $request->query->get('nivel_academico');
        if (!empty($academicLevel)) {
            $filters['nivelAcademicoId'] = $academicLevel;
        }

        $careers = $carreraRepository->findBy($filters, ['nombre' => 'ASC']);

        return new JsonResponse([
            'data' => $normalizer->normalize($careers, 'json', ['attributes' => ['id', 'nombre']]),
        ]);
    }

    #[Route('/carreras', name: 'api.carreras.store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(CarreraType::class);
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

        /** @var Carrera $career */
        $career = $form->getData();
        $career->setActivo(true);
        $career->setIsCame(false);

        $this->em->persist($career);
        $this->em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Creado con éxito',
            'data' => [
                'id' => $career->getId(),
                'nombre' => $career->getNombre(),
            ],
        ]);
    }
}
