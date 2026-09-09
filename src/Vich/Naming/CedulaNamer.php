<?php

namespace App\Vich\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class CedulaNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        if ($mapping->getFileNamePropertyName() === 'cedulaIdentificacion2') {
            return sprintf('identificacion-fiscal2.%s', $object->getCedulaFile2()->guessExtension());
        }

        return sprintf('identificacion-fiscal.%s', $object->getCedulaFile()->guessExtension());
    }
}
