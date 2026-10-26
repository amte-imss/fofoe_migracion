<?php

namespace App\Service;

use App\Entity\Usuario;
use App\Event\InstitucionEvent;
use App\Event\Posgrado\ResidenteEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UsuarioManager implements UsuarioManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function update(Usuario $usuario): void
    {
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        if ($institucion = $usuario->getInstitucion()) {
            $this->dispatcher->dispatch(
                new InstitucionEvent($institucion),
                InstitucionEvent::DATOS_ACTUALIZADOS
            );
        } elseif ($residente = $usuario->getResidente()) {
            $this->dispatcher->dispatch(
                new ResidenteEvent($residente),
                ResidenteEvent::RESIDENTE_DATOS_ACTUALIZADOS
            );
        }
    }
}
