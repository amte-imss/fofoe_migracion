<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

use App\Normalizer\OficioMontosFileInterfaces;

final class OficioMontos extends AbstractDocument implements DocumentInterface, OficioMontosFileInterfaces
{
    const NAME = 'Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos';

    public function __construct(?string $fecha, string $descripcion, ?string $urlArchivo)
    {
        parent::__construct(self::NAME, $fecha, $descripcion, $urlArchivo);
    }
}
