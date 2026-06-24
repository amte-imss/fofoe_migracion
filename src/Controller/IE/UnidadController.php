<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Institucion;
use App\Entity\Unidad;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
class UnidadController extends DIEControllerController
{
    private const UNIDAD_ATTRIBUTES = ['id', 'nombre', 'claveUnidad', 'delegacion' => ['id'], 'esUmae'];

    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/api/unidad/umae', methods: ['GET'], name: 'ie.unidad.umae')]
    public function index(Request $request): Response
    {
        $unidades = $this->em->getRepository(Unidad::class)->getAllUMAEs();

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($unidades, 'json', [
                'attributes' => self::UNIDAD_ATTRIBUTES,
            ]),
        ]);
    }

    #[Route('/api/unidad/umae/no_selecs', methods: ['GET'], name: 'ie.unidad.umae_no_selecs')]
    public function umaesNoSelecs(Request $request): Response
    {
        /** @var Institucion $institucion */
        $institucion = $this->getUser()->getInstitucion();

        $unidades = $this->em->getRepository(Unidad::class)
            ->getAllUMAEsNoSelecs($institucion->getId(), $this->getUser());

        $entry = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => 'disabled_lock_ce_units', 'activo' => true]);

        if (($entry?->getValor() ?? '0') === '1') {
            return $this->jsonResponse(['object' => []]);
        }

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($unidades, 'json', [
                'attributes' => self::UNIDAD_ATTRIBUTES,
            ]),
        ]);
    }

    #[Route('/api/unidad/delegacion/{id}', methods: ['GET'], name: 'ie.unidad.delegacion')]
    public function delegacion(Request $request, int $id): Response
    {
        $unidades = $this->em->getRepository(Unidad::class)
            ->getAllUnidadesByDelegacion($id, false);

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($unidades, 'json', [
                'attributes' => self::UNIDAD_ATTRIBUTES,
            ]),
        ]);
    }
}
