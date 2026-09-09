<?php

namespace App\Vich\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class ComprobantePagoNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        return sprintf(
            '%s_%s-comprobantepago.%s',
            $object->getReferenciaBancaria(),
            $object->getId(),
            $object->getComprobantePagoFile()->guessExtension()
        );
    }
}
