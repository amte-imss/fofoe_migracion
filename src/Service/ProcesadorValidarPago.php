<?php

namespace App\Service;

use App\Calculator\ComprobantePagoCalculatorInterface;
use App\Entity\CampoClinico;
use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Entity\Usuario;
use App\Event\PagoEvent;
use App\Repository\CampoClinicoRepositoryInterface;
use App\Repository\EstatusCampoRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class ProcesadorValidarPago implements ProcesadorValidarPagoInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ComprobantePagoCalculatorInterface $calculator,
        private readonly EstatusCampoRepositoryInterface $estatusCampoRepository,
        private readonly CampoClinicoRepositoryInterface $campoClinicoRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
        private readonly string $sender,
        private readonly MailerInterface $mailer,
        private readonly Environment $templating
    ) {
    }

    public function procesar(Pago $pago): void
    {
        $solicitud = $pago->getSolicitud();

        if ($solicitud->isPagoUnico()) {
            if ($pago->isValidado()) {
                $this->updateEstadoCamposClinicosPorPagoUnico($pago, $solicitud);
                if (!$pago->isRequiereFactura()) {
                    $this->setEstatusCredencialesGeneradasToSolicitud($solicitud);
                }
            } else {
                $this->updateEstadoCamposClinicosAPagoNoValido($solicitud);
                $this->setEstatusPagoNoValidoToSolicitud($solicitud);
            }
        } else {
            if ($pago->isValidado()) {
                $this->updateEstatusCampoClinicoPorPagoMultiple($pago);
                if (!$this->existenCamposConEstatusDiferenteACredencialesGeneradas($solicitud)) {
                    $this->setEstatusCredencialesGeneradasToSolicitud($solicitud);
                }
            } else {
                $camposClinico = $this->getCampoClinicoActual($pago);
                $estatus = $this->getEstatusCampoNoValido();
                $camposClinico->setEstatus($estatus);
            }
        }

        $this->notifyIE($pago);

        if (!$pago->isValidado()) {
            $this->createPago($pago);
        }
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new PagoEvent($pago),
            $pago->isValidado() ? PagoEvent::PAGO_VALIDADO : PagoEvent::PAGO_INCORRECTO
        );
    }

    private function updateEstadoCamposClinicosPorPagoUnico(
        Pago $pago,
        Solicitud $solicitud
    ): void {
        array_map(function (CampoClinico $campoClinico) use ($pago): void {
            $estatus = $this->getEstatusCampoByPagoValidado($pago);
            $campoClinico->setEstatus($estatus);
        }, $solicitud->getCamposClinicos()->toArray());
    }

    private function getEstatusCampoByPagoValidado(Pago $pago): ?EstatusCampo
    {
        return $this->estatusCampoRepository->findOneBy([
            'nombre' => $pago->isRequiereFactura() ?
                EstatusCampoInterface::PENDIENTE_FACTURA_FOFOE :
                EstatusCampoInterface::CREDENCIALES_GENERADAS
        ]);
    }

    private function updateEstatusCampoClinicoPorPagoMultiple(Pago $pago): void
    {
        $camposClinico = $this->getCampoClinicoActual($pago);
        $estatus = $this->getEstatusCampoByPagoValidado($pago);
        $camposClinico->setEstatus($estatus);
    }

    private function createPago(Pago $pago): void
    {
        $newPago = new Pago();
        $newPago->setRequiereFactura(false);
        $newPago->setSolicitud($pago->getSolicitud());
        $newPago->setReferenciaBancaria($pago->getReferenciaBancaria());
        $newPago->setMonto($this->calculator->getMontoAPagar($pago));
        $pago->getSolicitud()->addPago($pago);
        $this->entityManager->persist($newPago);
    }

    private function updateEstadoCamposClinicosAPagoNoValido(Solicitud $solicitud): void
    {
        array_map(function (CampoClinico $campoClinico): void {
            /** @var EstatusCampo $estatus */
            $estatus = $this->getEstatusCampoNoValido();
            $campoClinico->setEstatus($estatus);
        }, $solicitud->getCamposClinicos()->toArray());
    }

    private function getEstatusCampoNoValido(): ?EstatusCampo
    {
        return $this->estatusCampoRepository->findOneBy([
            'nombre' => EstatusCampoInterface::PAGO_NO_VALIDO
        ]);
    }

    private function setEstatusCredencialesGeneradasToSolicitud(Solicitud $solicitud): Solicitud
    {
        return $solicitud->setEstatus(SolicitudInterface::CREDENCIALES_GENERADAS);
    }

    private function setEstatusPagoNoValidoToSolicitud(Solicitud $solicitud): Solicitud
    {
        return $solicitud->setEstatus(SolicitudInterface::CARGANDO_COMPROBANTES);
    }

    private function existenCamposConEstatusDiferenteACredencialesGeneradas(Solicitud $solicitud): bool
    {
        return count(array_filter($solicitud->getCamposClinicos()->toArray(), function (CampoClinico $campoClinico): bool {
                $estatus = $campoClinico->getEstatus();
                return $campoClinico->getLugaresAutorizados() > 0
                    && $estatus && $estatus->getNombre() !== EstatusCampoInterface::CREDENCIALES_GENERADAS;
            })) !== 0;
    }

    private function getCampoClinicoActual(Pago $pago): ?CampoClinico
    {
        return $this->campoClinicoRepository->findOneBy([
            'referenciaBancaria' => $pago->getReferenciaBancaria()
        ]);
    }

    private function notifyIE(Pago $pago): void
    {
        $solicitud = $pago->getSolicitud();
        /** @var Institucion $institucion */
        $institucion = $solicitud->getInstitucion();
        $user = $institucion->getUsuario();
        if (!$user) {
            $userRepository = $this->entityManager->getRepository(Usuario::class);
            $user = $userRepository->findOneBy([
                'institucion' => $institucion
            ]);
        }
        $this->sendEmailValidacionPago($solicitud, $pago, $user);
    }

    private function sendEmailValidacionPago(Solicitud $solicitud, Pago $pago, ?Usuario $usuario = null): void
    {
        if (!$usuario || !$usuario->getCorreo()) {
            return;
        }

        $email = (new Email())
            ->subject('Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud())
            ->from($this->sender)
            ->to($usuario->getCorreo())
            ->html(
                $this->templating->render('emails/fofoe/ie_comprobante_revisado.html.twig', [
                    'solicitud' => $solicitud,
                    'pago' => $pago,
                    'usuario' => $usuario
                ])
            );
        $this->mailer->send($email);
    }
}
