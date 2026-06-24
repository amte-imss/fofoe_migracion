<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Factura;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class FacturaController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly DownloadHandler $downloadHandler,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/factura/{factura_id}/download', methods: ['GET'], name: 'came.factura.download')]
    public function downloadFile(int $factura_id): Response
    {
        $factura = $this->em->getRepository(Factura::class)->find($factura_id);

        if (!$factura) {
            throw $this->createNotFoundException('Not found for id ' . $factura_id);
        }

        if (
            !$this->isGranted('ROLE_CAME')
            && !$this->isGranted('ROLE_JDES')
            && !$this->isGranted('ROLE_JDES_MINUS')
            && !$this->isGranted('ROLE_CAME_MINUS')
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->downloadHandler->downloadObject($factura, 'zipFile');
    }
}
