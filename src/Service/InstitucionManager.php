<?php

namespace App\Service;

use App\Entity\Institucion;
use App\Event\InstitucionEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InstitucionManager implements InstitucionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function update(Institucion $institucion): void
    {
        $this->updateDataUsuarioInstitucion($institucion);
        $institucion->setDireccion(random_int(0, 10000) . 'as');
        $this->entityManager->persist($institucion);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new InstitucionEvent($institucion),
            InstitucionEvent::DATOS_ACTUALIZADOS
        );
    }

    private function updateDataUsuarioInstitucion(Institucion $institucion): void
    {
        $usuario = $institucion->getUsuario();
        if (!$usuario) {
            return;
        }

        $representante = trim($institucion->getRepresentante());
        $pos           = strpos($representante, ' ');
        $name          = explode(' ', $representante)[0];
        $last_name     = $pos !== false ? substr($representante, $pos + 1, 50) : '';

        $usuario->setCorreo($institucion->getCorreo());
        $usuario->setNombre(substr($name, 0, 250));
        $usuario->setApellidoPaterno($last_name);
        $usuario->setApellidoMaterno('');

        $this->entityManager->persist($usuario);
    }
}
