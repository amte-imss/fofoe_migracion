<?php

namespace App\Event;

use App\Entity\Institucion;
use Symfony\Contracts\EventDispatcher\Event;

class InstitucionEvent extends Event
{
    const DATOS_ACTUALIZADOS = 'institucion.datos_actualizados';
    const CIF_ACTUALIZADO    = 'institucion.cif_actualizado';

    public function __construct(
        private readonly Institucion $institucion
    ) {}

    public function getInstitucion(): Institucion
    {
        return $this->institucion;
    }
}
