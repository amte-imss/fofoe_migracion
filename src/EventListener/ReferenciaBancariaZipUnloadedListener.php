<?php

namespace App\EventListener;

use App\Entity\CampoClinico;
use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Event\ReferenciaBancariaZipUnloadedEvent;
use Doctrine\ORM\EntityManagerInterface;

class ReferenciaBancariaZipUnloadedListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function referenciaBancariaZipUnloaded(ReferenciaBancariaZipUnloadedEvent $event): void
    {
        $solicitud = $event->getSolicitud();

        $solicitud->setEstatus(SolicitudInterface::CARGANDO_COMPROBANTES);
        $this->setPendienteDePagoEstatusACamposClinicos($solicitud);
        $this->entityManager->flush();
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
    }
}
