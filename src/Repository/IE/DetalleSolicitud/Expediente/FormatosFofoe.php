<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

final class FormatosFofoe extends AbstractDocument implements DocumentInterface
{
    const NAME = 'Formato de cálculo de cuotas de recuperación';

    public function __construct(?string $fecha, string $descripcion, ?string $urlArchivo)
    {
        parent::__construct(self::NAME, $fecha, $descripcion, $urlArchivo);
    }
}
