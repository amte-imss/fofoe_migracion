<?php

namespace App\Vich\Naming;

use App\Entity\CampoClinico;
use App\Entity\Factura;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Solicitud;
use App\Repository\InstitucionRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class InstitucionDirectoryNamer implements DirectoryNamerInterface
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $institucionRepository,
        private readonly TokenStorageInterface          $tokenStorage,
    ) {}

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof Institucion) {
            return (string) $object->getId();
        }

        if ($object instanceof Factura) {
            if ($object->getPago()?->getSolicitud()) {
                $id = $object->getPago()->getSolicitud()->getId();
            } else {
                return 'facturas' . $object->getId();
            }
        } elseif ($object instanceof Pago && $object->getSolicitud()) {
            $id = $object->getSolicitud()->getId();
        } elseif ($object instanceof Solicitud) {
            $id = $object->getId();
        } elseif ($object instanceof CampoClinico) {
            $id = $object->getSolicitud()->getId();
        } else {
            return '';
        }

        /** @var Institucion $institucion */
        $institucion = $this->institucionRepository->getInstitucionBySolicitudId($id);

        return (string) $institucion->getId();
    }
}
