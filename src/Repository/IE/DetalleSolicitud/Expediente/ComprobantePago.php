<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

use App\Normalizer\ComprobantePagoFileInterface;

final class ComprobantePago extends AbstractDocument implements DocumentInterface, ComprobantePagoFileInterface
{
    const NAME = 'Comprobante de pago';

    public function __construct(?string $fecha, string $descripcion, ?string $urlArchivo, mixed $options = null)
    {
        parent::__construct(self::NAME, $fecha, $descripcion, $urlArchivo, $options);
    }
}
