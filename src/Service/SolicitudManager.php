<?php

namespace App\Service;

use App\Calculator\CampoClinicoCalculator2025;
use App\Calculator\CampoClinicoCalculatorInterface;
use App\Entity\CampoClinico;
use App\Entity\DescuentoMonto;
use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use App\Entity\Institucion;
use App\Entity\MontoCarrera;
use App\Entity\Permiso;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Entity\Usuario;
use App\Event\SolicitudEvent;
use App\Repository\UserRepositoryInterface;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment as TwigEnvironment;

class SolicitudManager implements SolicitudManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly LoggerInterface             $logger,
        private readonly MailerInterface             $mailer,
        private readonly TwigEnvironment             $twig,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface    $dispatcher,
        private readonly string                      $mailerSender,
        private readonly CampoClinicoCalculatorInterface $calculator,
        private readonly UserRepositoryInterface     $userRepository,
    ) {}

    public function update(Solicitud $solicitud): array
    {
        $this->entityManager->persist($solicitud);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
        return ['status' => true];
    }

    public function create(Solicitud $solicitud): array
    {
        $solicitud->setEstatus(Solicitud::CREADA);
        $solicitud->setFecha(Carbon::now());
        try {
            $this->entityManager->persist($solicitud);
            $this->entityManager->flush();
            $solicitud->setNoSolicitud('NS_' . str_pad($solicitud->getId(), 6, '0', STR_PAD_LEFT));
            $this->entityManager->persist($solicitud);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new SolicitudEvent($solicitud), SolicitudEvent::SOLICITUD_CREADA);
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }

        return [
            'status'  => true,
            'message' => 'Solicitud almacenada con éxito',
            'object'  => [
                'id'           => $solicitud->getId(),
                'fecha'        => $solicitud->getFecha(),
                'no_solicitud' => $solicitud->getNoSolicitud(),
            ],
        ];
    }

    public function finalizar(Solicitud $solicitud, ?Usuario $came_user = null): array
    {
        $solicitud->setEstatus(SolicitudInterface::REGISTRADA);
        try {
            $this->entityManager->persist($solicitud);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }

        $this->dispatcher->dispatch(new SolicitudEvent($solicitud), SolicitudEvent::SOLICITUD_TERMINADA);
        $this->notifyCAME($solicitud);

        return ['status' => true];
    }

    public function validarRegistroSolicitud(Solicitud $solicitud, ?Usuario $came_user = null): array
    {
        $solicitud->setEstatus(SolicitudInterface::CONFIRMADA);
        try {
            $this->entityManager->persist($solicitud);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }

        $this->dispatcher->dispatch(new SolicitudEvent($solicitud), SolicitudEvent::SOLICITUD_VALIDADA);
        $this->notifyIE($solicitud);

        return $this->update($solicitud);
    }

    public function registrarMontos(Solicitud $solicitud, array $originalDescuentos = []): void
    {
        /** @var CampoClinico $cc */
        foreach ($solicitud->getCamposClinicos() as $cc) {
            if ($cc->getLugaresAutorizados() <= 0) continue;
            $monto = $cc->getMontoCarrera();
            $this->registrarDescuentos($monto, $originalDescuentos);
            /** @var MontoCarrera $monto */
            $monto->setCampoClinico($cc);
        }

        $solicitud->setEstatus(SolicitudInterface::EN_VALIDACION_DE_MONTOS_CAME);
        $this->entityManager->persist($solicitud);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SolicitudEvent($solicitud), SolicitudEvent::MONTOS_REGISTRADOS);
        $this->notifyCAME($solicitud);
    }

    public function validarMontos(Solicitud $solicitud, array $montos = [], bool $is_valid = false, ?Usuario $came_usuario = null, array $originalDescuentos = []): array
    {
        $solicitud->setValidado($is_valid);
        $this->actualizarDatosMontosValidados($solicitud, $montos);

        try {
            /** @var CampoClinico $campo */
            foreach ($solicitud->getCamposClinicos() as $campo) {
                if ($campo->getLugaresAutorizados() <= 0) continue;
                $monto = $campo->getMontoCarrera();
                if ($monto && !is_null($monto->getMontoInscripcion()) && !is_null($monto->getMontoColegiatura())) {
                    $this->registrarDescuentos($monto, $originalDescuentos);
                } else {
                    throw new \Exception('Montos no puede estar vacío');
                }
            }

            if ($is_valid) {
                $solicitud->setEstatus(Solicitud::MONTOS_VALIDADOS_CAME);
                $this->processMontos($solicitud);
            } else {
                $solicitud->setEstatus(Solicitud::MONTOS_INCORRECTOS_CAME);
                $this->updateStatusCampos($solicitud, EstatusCampoInterface::NUEVO);
            }

            $this->sendEmailRevisionMontos($solicitud, $came_usuario);
            $this->entityManager->persist($solicitud);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }

        $this->dispatcher->dispatch(
            new SolicitudEvent($solicitud),
            $is_valid ? SolicitudEvent::MONTOS_VALIDADOS : SolicitudEvent::MONTOS_INCORRECTOS
        );

        return ['status' => true];
    }

    private function updateStatusCampos(Solicitud $solicitud, string $nombreEstatusCampo): void
    {
        /** @var EstatusCampo $estatusCampo */
        $estatusCampo = $this->entityManager->getRepository(EstatusCampo::class)
            ->findOneBy(['nombre' => $nombreEstatusCampo]);

        /** @var CampoClinico $campo */
        foreach ($solicitud->getCampoClinicos() as $campo) {
            $campo->setFechaFormatoFofoe(null);
            $campo->setEstatus($estatusCampo);
            $this->entityManager->persist($campo);
        }
        $this->entityManager->flush();
    }

    public function sendEmailRevisionMontos(Solicitud $solicitud, ?Usuario $came_usuario): void
    {
        if (!$solicitud->getInstitucion()->getCorreo()) return;

        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $solicitud->getInstitucion()->getCorreo(),
            'emails/came/montos_revisados.html.twig',
            ['solicitud' => $solicitud, 'came' => $came_usuario]
        );
    }

    public function sendEmailMontosInvalidos(Solicitud $solicitud, ?Usuario $came_usuario): void
    {
        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $solicitud->getInstitucion()->getCorreo() ?: 'recipient@example.com',
            'emails/came/montos_invalidos.html.twig',
            ['solicitud' => $solicitud, 'came' => $came_usuario]
        );
    }

    public function notifyIE(Solicitud $solicitud): void
    {
        $user = $solicitud->getInstitucion()->getUsuario();
        $this->sendEmailValidacionRegistroSolicitud($solicitud, $user);
    }

    public function notifyCAME(Solicitud $solicitud): void
    {
        $user = $this->getCAMEOrJDESUser($solicitud);
        if (!$user) return;

        match ($solicitud->getEstatus()) {
            SolicitudInterface::REGISTRADA                    => $this->sendEmailRegistroSolicitud($solicitud, $user),
            SolicitudInterface::EN_VALIDACION_DE_MONTOS_CAME => $this->sendEmailRegistroMontosSolicitud($solicitud, $user),
            default                                          => null,
        };
    }

    public function sendEmailRegistroSolicitud(Solicitud $solicitud, Usuario $usuario): void
    {
        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $usuario->getCorreo() ?: 'recipient@example.com',
            'emails/ie/came_registro_solicitud.html.twig',
            ['solicitud' => $solicitud, 'usuario' => $usuario]
        );
    }

    public function sendEmailRegistroMontosSolicitud(Solicitud $solicitud, Usuario $usuario): void
    {
        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $usuario->getCorreo() ?: 'recipient@example.com',
            'emails/ie/came_registro_montos_solicitud.html.twig',
            ['solicitud' => $solicitud, 'usuario' => $usuario]
        );
    }

    public function sendEmailValidacionRegistroSolicitud(Solicitud $solicitud, Usuario $usuario): void
    {
        $this->sendEmail(
            'Sistema de Administración del FOFOE - Notificación de solicitud ' . $solicitud->getNoSolicitud(),
            $usuario->getCorreo() ?: 'recipient@example.com',
            'emails/came/solicitud_confirmada.html.twig',
            ['solicitud' => $solicitud, 'usuario' => $usuario]
        );
    }

    public function generateUser(Solicitud $solicitud, ?Usuario $came_usuario = null): void
    {
        $institucion    = $solicitud->getInstitucion();
        $nueva_password = substr(md5(mt_rand()), 0, 8);
        $ie_permiso     = $this->entityManager->getRepository(Permiso::class)->findOneBy(['clave' => 'IE']);
        $user_db        = $this->entityManager->getRepository(Usuario::class)->findOneBy(['correo' => $institucion->getCorreo()]);

        if (!$user_db) {
            $name      = explode(' ', $institucion->getRepresentante())[0];
            $pos       = strpos($institucion->getRepresentante(), ' ');
            $last_name = substr($institucion->getRepresentante(), $pos + 1, 50);

            $user = new Usuario();
            $user->setCorreo($institucion->getCorreo());
            $user->setNombre(substr($name, 0, 250));
            $user->setApellidoPaterno($last_name);
            $user->setCurp('0');
            $user->setRfc('0');
            $user->setSexo('0');
            $user->setFechaIngreso(Carbon::now());
            $user->setRegims(0);
            $user->setContrasena($this->passwordHasher->hashPassword($user, $nueva_password));
            $user->setActivo(true);

            $this->entityManager->persist($user);
            try {
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
                $this->logger->critical($e->getMessage());
            }
        } else {
            $user = $user_db;
            $user->setActivo(true);
            $nueva_password = '';
        }

        $this->entityManager->refresh($user);

        if (!$user->getPermisos()->contains($ie_permiso)) {
            $user->addPermiso($ie_permiso);
        }

        $this->entityManager->persist($user);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
        }

        $institucion->setUsuario($user);
        $this->entityManager->persist($institucion);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->sendEmailBienvenida($solicitud, $nueva_password, $came_usuario);
    }

    private function sendEmailBienvenida(Solicitud $solicitud, string $password, ?Usuario $came_usuario = null): void
    {
        $this->sendEmail(
            'Sistema de Administración del FOFOE',
            $solicitud->getInstitucion()->getCorreo() ?: 'recipient@example.com',
            'emails/came/institucion_bienvenida.html.twig',
            ['solicitud' => $solicitud, 'password' => $password, 'came' => $came_usuario]
        );
    }

    private function processMontos(Solicitud $solicitud): void
    {
        $calculator2025   = new CampoClinicoCalculator2025();
        $monto_solicitud  = 0.0;

        /** @var CampoClinico $campoClinico */
        foreach ($solicitud->getCampoClinicos() as $campoClinico) {
            $total_campo = $campoClinico->getLugaresAutorizados() > 0
                ? $calculator2025->getDetail($campoClinico)['total']
                : 0.0;

            $campoClinico->setMonto(round($total_campo, 2));
            $monto_solicitud += $total_campo;
            $this->entityManager->persist($campoClinico);
        }

        $solicitud->setMonto(round($monto_solicitud, 2));
        $this->entityManager->persist($solicitud);
        $this->entityManager->flush();
    }

    private function registrarDescuentos(MontoCarrera $monto, array $originalDescuentos): void
    {
        $this->entityManager->persist($monto);

        $descuentosRemover = $monto->getId() && array_key_exists($monto->getId(), $originalDescuentos)
            ? $originalDescuentos[$monto->getId()]
            : [];

        /** @var DescuentoMonto $descuento */
        foreach ($monto->getDescuentos() as $descuento) {
            if (!$descuento->getDescuentoInscripcion())   $descuento->setDescuentoInscripcion(0);
            if ($descuento->getDescuentoInscripcion() > 100) $descuento->setDescuentoInscripcion(100);
            if (!$descuento->getDescuentoColegiatura())   $descuento->setDescuentoColegiatura(0);
            if ($descuento->getDescuentoColegiatura() > 100) $descuento->setDescuentoColegiatura(100);
            if (!$descuento->getNumAlumnos())             $descuento->setNumAlumnos(0);

            if (
                ($descuento->getDescuentoInscripcion() + $descuento->getDescuentoColegiatura()) > 0 &&
                $descuento->getNumAlumnos() > 0
            ) {
                $descuento->setMontoCarrera($monto);
                $this->entityManager->persist($descuento);
                unset($descuentosRemover[$descuento->getId()]);
            }
        }

        foreach ($descuentosRemover as $descuento) {
            $this->entityManager->remove($descuento);
        }

        $this->entityManager->flush();
    }

    private function actualizarDatosMontosValidados(Solicitud $solicitud, array $montos): void
    {
        /** @var CampoClinico $campo */
        foreach ($solicitud->getCamposClinicos() as $campo) {
            if ($campo->getLugaresAutorizados() <= 0) continue;
            if (!array_key_exists($campo->getId(), $montos)) {
                throw new \Exception('Se debe registrar montos para todos los campos clínicos');
            }
        }
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

    private function getCAMEOrJDESUser(Solicitud $solicitud): ?Usuario
    {
        if (!$solicitud->getEsUMAE()) {
            return $this->userRepository->getCameByDelegacion($solicitud->getDelegacion()->getId());
        }
        return $this->userRepository->getJDESByUnidad($solicitud->getUnidad()->getId());
    }
}
