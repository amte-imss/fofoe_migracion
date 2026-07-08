<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

use App\Normalizer\FacturaFileInterface;

final class Factura extends AbstractDocument implements DocumentInterface, FacturaFileInterface
{
    const NAME = 'Factura (CFDI)';

    public function __construct(?string $fecha, string $descripcion, ?string $urlArchivo, mixed $options = null)
    {
        parent::__construct(self::NAME, $fecha, $descripcion, $urlArchivo, $options);
    }
}
