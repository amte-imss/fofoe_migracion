<?php

namespace App\Vich\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class ComprobanteInscripcionNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        return sprintf(
            '%s_comprobante-inscripcion.%s',
            $object->getNoSolicitud(),
            $object->getUrlArchivoFile()->guessExtension()
        );
    }
}
