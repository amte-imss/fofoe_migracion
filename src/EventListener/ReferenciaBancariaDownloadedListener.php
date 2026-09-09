<?php

namespace App\EventListener;

use App\Entity\CampoClinico;
use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Event\ReferenciaBancariaDownloadedEvent;
use Doctrine\ORM\EntityManagerInterface;

class ReferenciaBancariaDownloadedListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function handleReferenciaBancariaDownloaded(ReferenciaBancariaDownloadedEvent $event): void
    {
        $solicitud = $event->getSolicitud();

        if ($solicitud->getEstatus() !== SolicitudInterface::FORMATOS_DE_PAGO_GENERADOS) {
            return;
        }

        $solicitud->setEstatus(SolicitudInterface::CARGANDO_COMPROBANTES);
        $this->entityManager->flush();

        $this->setPendienteDePagoEstatusACamposClinicos($solicitud);
    }

    protected function setPendienteDePagoEstatusACamposClinicos(Solicitud $solicitud): void
    {
        /** @var EstatusCampo $pendienteDePagoEstatus */
        $pendienteDePagoEstatus = $this->entityManager
            ->getRepository(EstatusCampo::class)
            ->findOneBy(['nombre' => EstatusCampoInterface::PENDIENTE_DE_PAGO]);

        /** @var CampoClinico $camposClinico */
        foreach ($solicitud->getCamposClinicos() as $camposClinico) {
            $camposClinico->setEstatus($pendienteDePagoEstatus);
        }

        $this->entityManager->flush();
    }
}
