<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Delegacion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DelegacionController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/came/api/delegacion', methods: ['GET'], name: 'came.delegacion.index')]
    public function index(Request $request): Response
    {
        $delegaciones = $this->em->getRepository(Delegacion::class)
            ->findBy(['activo' => true]);

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($delegaciones, 'json', [
                'attributes' => ['id', 'nombre'],
            ]),
        ]);
    }
}
