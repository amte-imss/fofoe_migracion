<?php

namespace App\Service\Fofoe;

use App\Calculator\ComprobantePagoCalculatorInterface;
use App\Entity\Pago;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Entity\Usuario;
use App\Event\PagoEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

final class ValidarPagoPosgrado implements ValidarPagoPosgradoInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ComprobantePagoCalculatorInterface $calculator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly string $sender,
    ) {}

    public function procesar(Pago $pago, ?float $tasaCambio = null): void
    {
        $residencia = $pago->getResidencia();
        $residencia->setTasaCambio($tasaCambio);

        $montoPorPagar = $this->calculator->getMontoAPagar($pago);
        $isPagoCompleto = $pago->isValidado() && $montoPorPagar <= $pago->getMonto();

        $residencia->setEstatus(match (true) {
            !$isPagoCompleto => ResidenciaInterface::PAGO_NO_VALIDO,
            $pago->isRequiereFactura() => ResidenciaInterface::PENDIENTE_FACTURA_FOFOE,
            default => ResidenciaInterface::PAGO_VALIDADO_FOFOE,
        });

        if (!$isPagoCompleto) {
            $this->createPago($pago);
        }

        $this->entityManager->flush();

        $this->notifyResidente($pago);

        $this->dispatcher->dispatch(
            new PagoEvent($pago),
            $isPagoCompleto ? PagoEvent::PAGO_VALIDADO : PagoEvent::PAGO_INCORRECTO
        );
    }

    private function createPago(Pago $pago): void
    {
        $residencia = $pago->getResidencia();

        $newPago = new Pago();
        $newPago->setRequiereFactura(false);
        $newPago->setResidencia($residencia);
        $newPago->setReferenciaBancaria($pago->getReferenciaBancaria());
        $newPago->setMonto($this->calculator->getMontoAPagar($pago));

        $residencia->addPago($newPago);
        $this->entityManager->persist($newPago);
    }

    private function notifyResidente(Pago $pago): void
    {
        $residencia = $pago->getResidencia();
        $usuario = $residencia->getResidente()?->getUsuario();

        if (!$usuario?->getCorreo()) {
            return;
        }

        $this->sendEmailValidacionPago($residencia, $pago, $usuario);
    }

    private function sendEmailValidacionPago(Residencia $residencia, Pago $pago, Usuario $usuario): void
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($usuario->getCorreo())
            ->subject('Sistema de Administración del FOFOE - Notificación de referencia ' . $residencia->getFolio())
            ->html($this->twig->render('emails/fofoe/residente_comprobante_revisado.html.twig', [
                'residencia' => $residencia,
                'pago' => $pago,
                'usuario' => $usuario,
            ]));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Could not send payment validation email', [
                'pago_id' => $pago->getId(),
                'residencia_id' => $residencia->getId(),
                'exception' => $e,
            ]);
        }
    }
}
