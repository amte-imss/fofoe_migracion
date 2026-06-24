<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Pago;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class PagoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly DownloadHandler $downloadHandler,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pago/{pago_id}/download', methods: ['GET'], name: 'pago.comprobante')]
    public function downloadComprobante(int $pago_id): Response
    {
        $pago = $this->em->getRepository(Pago::class)->find($pago_id);

        if (!$pago) {
            throw $this->createNotFoundException('Not found for id ' . $pago_id);
        }

        return $this->downloadHandler->downloadObject($pago, 'comprobantePagoFile');
    }
}
