<?php

namespace App\Vich\Naming;

use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Posgrado\Residente;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class ComprobantePagoDirectoryNamer implements DirectoryNamerInterface
{
    public function __construct(
        private readonly string $institucionDirectory,
        private readonly string $residenteDirectory,
    ) {}

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof Pago) {
            return $this->directoryNamePago($object);
        }

        return '';
    }

    public function directoryNameInstitution(?Institucion $institucion): string
    {
        return $this->institucionDirectory . DIRECTORY_SEPARATOR . ($institucion?->getId() ?? '');
    }

    public function directoryNameResidente(?Residente $residente): string
    {
        return $this->residenteDirectory . DIRECTORY_SEPARATOR . ($residente?->getId() ?? '');
    }

    public function directoryNamePago(?Pago $pago): string
    {
        if ($pago?->getSolicitud()) {
            return $this->directoryNameInstitution($pago->getSolicitud()->getInstitucion());
        }

        if ($pago?->getResidencia()) {
            return $this->directoryNameResidente($pago->getResidencia()->getResidente());
        }

        return '';
    }
}
