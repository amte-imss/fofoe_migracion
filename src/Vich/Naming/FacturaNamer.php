<?php

namespace App\Vich\Naming;

use Carbon\Carbon;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class FacturaNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        return sprintf(
            '%s_%s-factura.%s',
            $object->getFolio(),
            Carbon::now()->format('Y-m-d_H_i_s'),
            $object->getZipFile()->guessExtension()
        );
    }
}
