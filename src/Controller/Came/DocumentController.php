<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Solicitud;
use App\Repository\SolicitudRepositoryInterface;
use App\Service\GeneradorFormatosFofoeZIPInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/came')]
final class DocumentController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly SolicitudRepositoryInterface $solicitudRepository,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/solicitud/{id}/descargar-formatos-fofoe', name: 'came#descargar_formatos_fofoe')]
    public function descargarFormatosFofoe(
        int $id,
        GeneradorFormatosFofoeZIPInterface $generadorFormatosFofoeZIP
    ): Response {
        /** @var Solicitud $solicitud */
        $solicitud = $this->solicitudRepository->find($id);

        if (!$solicitud) {
            throw $this->createNotFindSolicitudException($id);
        }
        if (!$this->isGrantedUserAccessToSolicitud($solicitud)) {
            throw $this->createAccessDeniedException('No tiene permisos para acceder a la solicitud ' . $id);
        }

        return $generadorFormatosFofoeZIP->generarZipResponse($solicitud);
    }
}
