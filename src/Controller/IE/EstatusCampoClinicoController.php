<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Repository\EstatusCampoRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EstatusCampoClinicoController extends DIEControllerController
{
    #[Route('/estatus-campos-clinicos', name: 'estatus_campos_clinicos#index')]
    public function index(EstatusCampoRepositoryInterface $repository): Response
    {
        return new Response(
            $this->serializer->serialize($repository->findAll(), 'json')
        );
    }
}
