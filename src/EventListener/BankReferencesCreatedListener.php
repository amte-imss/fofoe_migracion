<?php

namespace App\EventListener;

use App\Entity\SolicitudInterface;
use App\Event\BankReferencesCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;

class BankReferencesCreatedListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function handleBankReferencesCreated(BankReferencesCreatedEvent $event): void
    {
        $solicitud = $event->getSolicitud();

        if ($solicitud->getEstatus() !== SolicitudInterface::FORMATOS_DE_PAGO_GENERADOS) {
            return;
        }

        $solicitud->setEstatus(SolicitudInterface::CARGANDO_COMPROBANTES);
        $this->entityManager->flush();
    }
}
