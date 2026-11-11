<?php

namespace App\Controller\Admin\Camex;

use App\Controller\DIEControllerController;
use App\Entity\Campus;
use App\Entity\Institucion;
use App\Form\Type\Admin\Came\CampusType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/convenioscame/institucion')]
class CampusController extends DIEControllerController
{
    #[Route(
        '/{institucionId}/campus',
        name: 'api.came.institucion.campus.store',
        requirements: ['institucionId' => '\d+'],
        methods: ['POST']
    )]
    public function store(Request $request, int $institucionId): JsonResponse
    {
        $institucion = $this->em->getRepository(Institucion::class)->find($institucionId);

        if (!$institucion) {
            return $this->httpErrorResponse('Institucion no encontrada', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CampusType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $campus = new Campus();
            $campus->setNombre($data['name']);
            $campus->setInstitucion($institucion);

            $this->em->persist($campus);
            $this->em->flush();

            return new JsonResponse([
                'status'  => true,
                'campus'  => [
                    'id'     => $campus->getId(),
                    'nombre' => $campus->getNombre(),
                ],
                'message' => 'Campus guardado correctamente',
            ]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($form, true),
        ], Response::HTTP_BAD_REQUEST);
    }

    #[Route(
        '/{institucionId}/campus',
        name: 'api.came.institucion.campus.index',
        requirements: ['institucionId' => '\d+'],
        methods: ['GET']
    )]
    public function index(int $institucionId): JsonResponse
    {
        $institucion = $this->em->getRepository(Institucion::class)->find($institucionId);

        if (!$institucion) {
            return $this->httpErrorResponse('Institucion no encontrada', Response::HTTP_NOT_FOUND);
        }

        $campusList = $this->em->getRepository(Campus::class)->findBy(
            ['institucion' => $institucion],
            ['nombre' => 'ASC']
        );

        return new JsonResponse(
            $this->serializer->normalize($campusList, 'json', [
                'attributes' => ['id', 'nombre'],
            ])
        );
    }
}
