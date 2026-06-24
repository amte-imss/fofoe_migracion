<?php

namespace App\Service\Posgrado;

use App\Calculator\Posgrado\ResidenteExtranjeroNoImssCalculatorInterface;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Pago;
use App\Entity\Permiso;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Entity\Posgrado\Residente;
use App\Event\Posgrado\ResidenciaEvent;
use App\Repository\ConfiguracionGlobalRepository;
use App\Repository\Posgrado\ResidenteRepository;
use App\Service\GeneradorRefenciaBancaria2025;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as TwigEnvironment;

class ResidenciaExNoImssManager implements ResidenciaExNoImssManagerInterface
{
    const PREFIX_MATRICULA = 'RES_';

    private readonly ResidenteRepository $residenteRepository;
    private readonly ConfiguracionGlobalRepository $configuracionGlobalRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GeneradorRefenciaBancaria2025 $generadorReferenciaBancaria,
        private readonly ResidenteExtranjeroNoImssCalculatorInterface $residenteExtranjeroNoImssCalculator,
        private readonly MailerInterface $mailer,
        private readonly TwigEnvironment $twig,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $mailerSender = 'no_contestar@educacionensalud.imss.gob.mx',
    ) {
        $this->residenteRepository           = $entityManager->getRepository(Residente::class);
        $this->configuracionGlobalRepository = $entityManager->getRepository(ConfiguracionGlobal::class);
    }

    public function registrarResidenciaExNoImss(Residencia $residencia): void
    {
        $residente = $residencia->getResidente();
        $this->makeUsuarioForResidente($residencia);

        $residente->setEstatus('NUEVO');
        $residente->setFolio($residencia->getFolio());
        $residencia->setEstatus(ResidenciaInterface::NUEVO);

        $this->residenteExtranjeroNoImssCalculator->getMontoAPagar($residencia, true);

        $residencia->addPago($this->makePago($residencia));
        $this->entityManager->persist($residencia);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new ResidenciaEvent($residencia),
            ResidenciaEvent::RESIDENCIA_REGISTRADA
        );
    }

    public function actualizarResidenciaExNoImss(Residencia $residencia): void
    {
        $this->updateUsuarioForResidente($residencia);
        $this->residenteExtranjeroNoImssCalculator->getMontoAPagar($residencia, true);

        /** @var Pago $pago */
        $pago = $residencia->getLastPago();
        if (!$pago->getFechaPago()) {
            $pago->setMonto($residencia->getMonto());
            $pago->setTipoMoneda($residencia->getTipoMoneda());
        }

        $this->entityManager->persist($residencia);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new ResidenciaEvent($residencia),
            ResidenciaEvent::RESIDENCIA_ACTUALIZADA
        );
    }

    public function sendEmailBienvenida(Residencia $residencia): void
    {
        $to   = $residencia->getResidente()->getUsuario()->getCorreo();
        $html = $this->twig->render('emails/residente/nuevo_residente_ex_no_imss.html.twig', [
            'residencia' => $residencia,
        ]);

        $email = (new Email())
            ->from($this->mailerSender)
            ->to($to)
            ->bcc('zurgcom@gmail.com', 'eliarteaga1977@gmail.com')
            ->subject('Sistema de Administración del FOFOE - Bienvenido')
            ->html($html);

        $this->mailer->send($email);
    }

    private function makeUsuarioForResidente(Residencia $residencia): void
    {
        $residente       = $residencia->getResidente();
        $usuario         = $residente->getUsuario();
        $residentePermiso = $this->entityManager->getRepository(Permiso::class)
            ->findOneBy(['clave' => 'ALUMNO_POSGRADO']);

        $usuario->addPermiso($residentePermiso);

        $subfijo = substr(md5(uniqid((string) rand(), true)), 0, 8);
        $usuario->setMatricula($usuario->getCurp() . '_' . $subfijo);
        $usuario->setActivo(true);

        $password = substr(md5(Carbon::now()->toDateTimeString()), 0, 8);
        $usuario->setPlainPassword($password);
        $usuario->setContrasena(
            $this->passwordHasher->hashPassword($usuario, $password)
        );
    }

    private function updateUsuarioForResidente(Residencia $residencia): void
    {
        $residente = $residencia->getResidente();
        $usuario   = $residente->getUsuario();

        $usuario->setMatricula($usuario->getCurp());
        $usuario->setContrasena(
            $this->passwordHasher->hashPassword($usuario, $residente->getNacionalidad())
        );
    }

    private function makePago(Residencia $residencia): Pago
    {
        $pago = new Pago();
        $pago->setMonto($residencia->getMonto());
        $pago->setTipoMoneda($residencia->getTipoMoneda());
        $pago->setResidencia($residencia);
        $pago->setReferenciaBancaria(
            $this->generadorReferenciaBancaria->generateNextReference()
        );
        return $pago;
    }
}
