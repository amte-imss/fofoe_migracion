<?php

namespace App\EventListener\Posgrado;

use App\Entity\Posgrado\ResidenciaInterface;
use App\Event\Posgrado\ReferenciaBancariaResidenteDownloadedEvent;
use Doctrine\ORM\EntityManagerInterface;

class ReferenciaBancariaResidenteDownloadedListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function handleReferenciaBancariaDownloaded(ReferenciaBancariaResidenteDownloadedEvent $event): void
    {
        $residencia = $event->getResidencia();

        if ($residencia->getEstatus() && $residencia->getEstatus() !== ResidenciaInterface::NUEVO) {
            return;
        }

        $residencia->setEstatus(ResidenciaInterface::FORMATO_PAGO_DESCARGADO);
        $this->entityManager->flush();
    }
}
