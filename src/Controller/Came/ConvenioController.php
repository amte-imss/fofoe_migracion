<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Convenio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConvenioController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/came/api/convenio/{institucion_id}', methods: ['GET'], name: 'came.convenio.show')]
    public function show(Request $request, int $institucion_id): Response
    {
        $convenios = $this->em->getRepository(Convenio::class)
            ->getAllNivelesByConvenio($institucion_id);

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($convenios, 'json', [
                'attributes' => [
                    'cicloAcademico' => ['id', 'nombre'],
                    'id',
                    'vigencia', 'vigenciaFormatted',
                    'label', 'numero', 'tipo', 'ciclosAcademicosFormatted',
                    'carrera' => [
                        'id', 'nombre',
                        'nivelAcademico' => ['id', 'nombre'],
                    ],
                ],
            ]),
        ]);
    }
}
