<?php

namespace App\Vich\Naming;

use App\Entity\Enfermeria\Alumno;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class AlumnoEnfermeriaDirectoryNamer implements DirectoryNamerInterface
{
    public function __construct(
    ) {}

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof Alumno) {
            return (string) $object->getId();
        }

        return '';
    }
}
