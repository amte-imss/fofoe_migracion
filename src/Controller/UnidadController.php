<?php

namespace App\Controller;

use App\Entity\Unidad;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UnidadController extends DIEControllerController
{
    #[Route('/api/unidad/delegacion/{id}', name: 'unidad.delegacion', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delegacion(int $id, NormalizerInterface $normalizer): Response
    {
        $unidades = $this->em->getRepository(Unidad::class)->getAllUnidadesByDelegacion($id);

        if ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS')) {
            $userUnidadId = $this->getUserUnidad()?->getId();

            $unidades = array_values(array_filter(
                $unidades,
                static fn (Unidad $unidad): bool => $unidad->getId() === $userUnidadId,
            ));
        }

        return $this->jsonResponse([
            'object' => $normalizer->normalize($unidades, 'json', [
                'attributes' => ['id', 'nombre', 'claveUnidad', 'delegacion' => ['id'], 'esUmae'],
            ]),
        ]);
    }
}
