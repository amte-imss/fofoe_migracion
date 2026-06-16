<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Service\SIEDManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValidacionMatriculaController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route(
        '/came/api/validar_matricula/{matricula}/{delegacion}',
        methods: ['GET'],
        name: 'came.vaalidar_matricula',
        requirements: ['matricula' => '\d+', 'delegacion' => '\d+']
    )]
    public function validarMatricula(SIEDManager $SIEDManager, string $matricula, int $delegacion): Response
    {
        try {
            $sied = $SIEDManager->getDataFromSIEDByMatriculaYClaveDelegacional($matricula, $delegacion);

            return $this->jsonResponse([
                'object' => $this->normalizer->normalize($sied, 'json', [
                    'attributes' => [
                        'nombre', 'apaterno', 'amaterno', 'curp',
                        'unidad', 'adscripcion', 'correo', 'adscripcionId',
                    ],
                ]),
            ]);
        } catch (\Exception $exception) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
