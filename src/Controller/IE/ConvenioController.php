<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\Institucion;
use App\Normalizer\InstitucionPerfilNormalizerInterface;
use App\Repository\ConvenioRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ie')]
class ConvenioController extends DIEControllerController
{
    #[Route('/api/convenio', methods: ['GET'], name: 'ie.convenio')]
    public function index(Request $request, ConvenioRepositoryInterface $convenioRepository, InstitucionPerfilNormalizerInterface $institucionPerfilNormalizer): Response
    {
        /** @var Institucion $institucion */
        $institucion = $this->getUser()->getInstitucion();

        if (!$institucion) {
            $this->createNotFindUserRelationWithInstitucionException();
        }

        $convenios = $convenioRepository->getAllNivelesByConvenio($institucion->getId());

        return $this->jsonResponse([
            'object' => $institucionPerfilNormalizer->normalizeConvenios($convenios),
        ]);
    }
}
