<?php

namespace App\Vich\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class FormatoFofoeNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        return sprintf(
            '%s_%s-formatoFofoe.%s',
            $object->getSolicitud()->getNoSolicitud(),
            $object->getId(),
            $object->getFormatoFofoeFile()->guessExtension()
        );
    }
}
