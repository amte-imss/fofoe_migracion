<?php

namespace App\EventSubscriber\Posgrado;

use App\Entity\Posgrado\ResidenciaInterface;
use App\Event\Posgrado\ResidenciaEvent;
use App\EventSubscriber\AbstractSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;

class ResidenciaSubscriber extends AbstractSubscriber implements EventSubscriberInterface
{
    public function __construct(
        LoggerInterface          $logger,
        RequestStack             $requestStack,
        private readonly MailerInterface    $mailer,
        private readonly TwigEnvironment   $twig,
        private readonly string            $mailerSender,
    ) {
        parent::__construct($logger, $requestStack);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResidenciaEvent::RESIDENCIA_REGISTRADA  => 'onResidenciaRegistrada',
            ResidenciaEvent::RESIDENCIA_ACTUALIZADA => 'onResidenciaActualizada',
        ];
    }

    public function onResidenciaRegistrada(ResidenciaEvent $event): void
    {
        $residencia = $event->getResidencia();

        $template = $residencia->getTipo() === ResidenciaInterface::TIPO_EXTRANJERO_IMSS
            ? 'emails/residente/nuevo_residente_ex_imss.html.twig'
            : 'emails/residente/nuevo_residente_ex_no_imss.html.twig';

        $email = (new Email())
            ->from($this->mailerSender)
            ->to($residencia->getResidente()->getUsuario()->getCorreo())
            ->subject('Sistema de Administración del FOFOE - Bienvenido')
            ->html($this->twig->render($template, ['residencia' => $residencia]));

        $this->mailer->send($email);

        $this->logDB(ResidenciaInterface::NUEVO, [
            'residencia_id' => $residencia->getId(),
            'folio'         => $residencia->getFolio(),
            'tipo'          => $residencia->getTipo(),
            'ciclo'         => $residencia->getCiclo(),
        ]);
    }

    public function onResidenciaActualizada(ResidenciaEvent $event): void
    {
        $residencia = $event->getResidencia();

        $this->logDB('Residencia actualizada', [
            'residencia_id' => $residencia->getId(),
            'folio'         => $residencia->getFolio(),
            'tipo'          => $residencia->getTipo(),
            'ciclo'         => $residencia->getCiclo(),
        ]);
    }
}
