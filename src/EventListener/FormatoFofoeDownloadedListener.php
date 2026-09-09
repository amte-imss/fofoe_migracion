<?php

namespace App\EventListener;

use App\Entity\CampoClinico;
use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use App\Entity\Permiso;
use App\Entity\SolicitudInterface;
use App\Entity\Usuario;
use App\Event\FormatoFofofeDownloadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FormatoFofoeDownloadedListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface  $tokenStorage,
    ) {}

    public function handleFormatoFofoeDownloaded(FormatoFofofeDownloadedEvent $event): void
    {
        $this->udpateStatusCampoClinico($event->getCampoClinico());
    }

    protected function udpateStatusCampoClinico(CampoClinico $campoClinico): void
    {
        /** @var Usuario $user */
        $user         = $this->tokenStorage->getToken()->getUser();
        $permisoCame  = $this->entityManager->getRepository(Permiso::class)->findOneBy(['clave' => 'CAME']);
        $permisoJdes  = $this->entityManager->getRepository(Permiso::class)->findOneBy(['clave' => 'JDES']);

        if (
            $user &&
            !$user->getPermisos()->contains($permisoCame) &&
            !$user->getPermisos()->contains($permisoJdes)
        ) {
            if (
                is_null($campoClinico->getEstatus()) ||
                $campoClinico->getEstatus()->getNombre() === EstatusCampoInterface::NUEVO
            ) {
                /** @var EstatusCampo $estatus */
                $estatus = $this->entityManager->getRepository(EstatusCampo::class)
                    ->findOneBy(['nombre' => EstatusCampoInterface::PENDIENTE_DE_PAGO]);

                $campoClinico->setEstatus($estatus);
                $this->entityManager->persist($campoClinico);
                $this->entityManager->flush();
            }
        }

        $solicitud = $campoClinico->getSolicitud();

        if (
            $this->userIsCAMEorJDES($user) &&
            $solicitud->getEstatus() === SolicitudInterface::EN_VALIDACION_DE_MONTOS_CAME
        ) {
            $this->setFormatoFofoeDescargadoEstatusACampoClinico($campoClinico);
        }
    }

    private function userIsCAMEorJDES(mixed $user): bool
    {
        $permisoCame = $this->entityManager->getRepository(Permiso::class)->findOneBy(['clave' => 'CAME']);
        $permisoJdes = $this->entityManager->getRepository(Permiso::class)->findOneBy(['clave' => 'JDES']);

        return $user && (
                $user->getPermisos()->contains($permisoCame) ||
                $user->getPermisos()->contains($permisoJdes)
            );
    }

    protected function setFormatoFofoeDescargadoEstatusACampoClinico(CampoClinico $campoClinico): void
    {
        /** @var EstatusCampo $formatoFofofeDescargadoEstatus */
        $formatoFofofeDescargadoEstatus = $this->entityManager
            ->getRepository(EstatusCampo::class)
            ->findOneBy(['nombre' => EstatusCampoInterface::FORMATO_FOFOE_DESCARGADO]);

        $campoClinico->setEstatus($formatoFofofeDescargadoEstatus);
        $this->entityManager->flush();
    }
}
