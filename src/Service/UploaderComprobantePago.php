<?php

namespace App\Service;

use App\Entity\Factura;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Posgrado\Residencia;
use App\Entity\Solicitud;
use App\Entity\Usuario;
use App\Repository\PagoRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;

class UploaderComprobantePago implements UploaderComprobantePagoInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PagoRepositoryInterface $pagoRepository,
        private readonly LoggerInterface         $logger,
        private readonly string                  $mailerSender,
        private readonly string                  $mailSendToFofoe,
        private readonly MailerInterface         $mailer,
        private readonly TwigEnvironment         $twig,
    ) {}

    public function update(Pago $pago): bool
    {
        $this->logger->info(sprintf(
            'Iniciado el guardado del comprobante de pago para el pago con id %s',
            $pago->getId()
        ));

        $file = $pago->getComprobantePagoFile();
        $pago->setComprobantePagoFile(null);
        $this->entityManager->flush();

        $pago->setComprobantePagoFile($file);
        $this->entityManager->flush();
        $this->notifyFOFOE($pago);

        return true;
    }

    public function sendEmailRegistroFactura(Solicitud $solicitud, Pago $pago, Factura $factura): void
    {
        /** @var Institucion $institucion */
        $institucion = $solicitud->getInstitucion();
        /** @var Usuario $user */
        $user = $institucion->getUsuario();

        if (!$user || !$user->getCorreo()) {
            return;
        }

        $solicitud = $pago->getSolicitud();

        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $user->getCorreo(),
            'emails/fofoe/ie_factura_generada.html.twig',
            ['pago' => $pago, 'solicitud' => $solicitud]
        );
    }

    private function notifyFOFOE(Pago $pago): void
    {
        if ($pago->getSolicitud()) {
            $this->sendEmailRegistroPago($pago);
        } elseif ($pago->getResidencia()) {
            $this->sendEmailRegistroPagoResidencia($pago);
        }
    }

    private function sendEmailRegistroPago(Pago $pago): void
    {
        $solicitud = $pago->getSolicitud();

        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $this->mailSendToFofoe,
            'emails/ie/fofoe_carga_comprobante_pago.html.twig',
            ['pago' => $pago, 'solicitud' => $solicitud]
        );
    }

    private function sendEmailRegistroPagoResidencia(Pago $pago): void
    {
        /** @var Residencia $residencia */
        $residencia = $pago->getResidencia();

        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de residencia ' . $residencia->getFolio(),
            $this->mailSendToFofoe,
            'emails/residente/fofoe_carga_comprobante_pago.html.twig',
            ['pago' => $pago, 'residencia' => $residencia, 'residente' => $residencia->getResidente()]
        );
    }

    private function sendEmail(string $subject, string $to, string $template, array $context): void
    {
        $email = (new Email())
            ->from($this->mailerSender)
            ->to($to)
            ->subject($subject)
            ->html($this->twig->render($template, $context));

        $this->mailer->send($email);
    }
}
