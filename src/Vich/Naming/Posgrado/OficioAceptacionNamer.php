<?php

namespace App\Vich\Naming\Posgrado;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class OficioAceptacionNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        return sprintf('oficio-aceptacion_%s.%s',
            $object->getFolio(),
            $object->getOficioFile()->guessExtension()
        );
    }
}
