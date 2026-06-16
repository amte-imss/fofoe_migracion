<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Delegacion;
use App\Entity\Institucion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
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

    #[Route('/api/delegacion', methods: ['GET'], name: 'ie.delegacion.index')]
    public function index(Request $request): Response
    {
        $delegaciones = $this->em->getRepository(Delegacion::class)->findAll();

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($delegaciones, 'json', [
                'attributes' => ['id', 'nombre', 'activo'],
            ]),
        ]);
    }

    #[Route('/api/delegacion/convenios', methods: ['GET'], name: 'ie.delegacion.convenioss')]
    public function indexWithConvenios(Request $request): Response
    {
        /** @var Institucion $institucion */
        $institucion  = $this->getUser()->getInstitucion();
        $delegaciones = $this->em->getRepository(Delegacion::class)
            ->getAllWithConvenio($institucion->getId());

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($delegaciones, 'json', [
                'attributes' => ['id', 'nombre', 'activo'],
            ]),
        ]);
    }

    #[Route('/api/delegacion/no_selecs', methods: ['GET'], name: 'ie.delegacion.no_selecs')]
    public function delegacionesNoSelecs(Request $request): Response
    {
        /** @var Institucion $institucion */
        $institucion  = $this->getUser()->getInstitucion();
        $delegaciones = $this->em->getRepository(Delegacion::class)
            ->getAllDelsNoSelecs($institucion->getId(), $this->getUser());

        $entry = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => 'disabled_lock_ce_units', 'activo' => true]);

        if (($entry?->getValor() ?? '0') === '1') {
            return $this->jsonResponse(['object' => []]);
        }

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($delegaciones, 'json', [
                'attributes' => ['id', 'nombre'],
            ]),
        ]);
    }
}
