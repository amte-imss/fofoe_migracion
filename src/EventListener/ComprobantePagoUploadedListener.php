<?php

namespace App\EventListener;

use App\Entity\CampoClinico;
use App\Entity\EstatusCampoInterface;
use App\Entity\Pago;
use App\Entity\SolicitudInterface;
use App\Repository\CampoClinicoRepositoryInterface;
use App\Repository\EstatusCampoRepositoryInterface;
use App\Repository\PagoRepositoryInterface;
use App\Repository\SolicitudRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Event\Event;

class ComprobantePagoUploadedListener
{
    public function __construct(
        private readonly EntityManagerInterface          $entityManager,
        private readonly EstatusCampoRepositoryInterface $estatusCampoRepository,
        private readonly CampoClinicoRepositoryInterface $campoClinicoRepository,
        private readonly SolicitudRepositoryInterface    $solicitudRepository,
        private readonly PagoRepositoryInterface         $pagoRepository,
    ) {}

    public function comprobantePagoUploaded(Event $event): void
    {
        if (!$event->getObject() instanceof Pago) {
            return;
        }

        /** @var Pago $pago */
        $pago = $event->getObject();

        if (!$pago->getSolicitud()) {
            return;
        }

        $estatusPagado = $this->estatusCampoRepository->getEstatusPagado();

        if ($pago->getSolicitud()->isPagoUnico()) {
            foreach ($pago->getSolicitud()->getCamposClinicos() as $camposClinico) {
                /** @var CampoClinico $camposClinico */
                $camposClinico->setEstatus($estatusPagado);
            }
            $this->settingSolicitudEnValidacionFOFOE($pago);
        } else {
            $this->actualizarEstatusDeCampoClinicoActual($pago, $estatusPagado);
            if ($this->isSolicitudListaParaCambiarDeEstatus($pago)) {
                $this->settingSolicitudEnValidacionFOFOE($pago);
            }
        }

        $this->entityManager->flush();
    }

    private function isSolicitudListaParaCambiarDeEstatus(Pago $pago): bool
    {
        return count($this->getCamposClinicosSinComprobantesDePagoCargados($pago)) === 0;
    }

    private function getCamposClinicosSinComprobantesDePagoCargados(Pago $pago): array
    {
        return array_filter(
            $pago->getSolicitud()->getCamposClinicos()->toArray(),
            fn(CampoClinico $campoClinico) => $campoClinico->getEstatus()->getNombre() === EstatusCampoInterface::PENDIENTE_DE_PAGO
        );
    }

    private function actualizarEstatusDeCampoClinicoActual(Pago $pago, mixed $estatusPagado): void
    {
        $camposClinico = $this->campoClinicoRepository->findOneBy([
            'referenciaBancaria' => $pago->getReferenciaBancaria(),
        ]);
        $camposClinico->setEstatus($estatusPagado);
    }

    private function settingSolicitudEnValidacionFOFOE(Pago $pago): void
    {
        $pago->getSolicitud()->setEstatus(SolicitudInterface::EN_VALIDACION_FOFOE);
    }
}
