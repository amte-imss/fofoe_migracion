<?php

namespace App\Normalizer;

use App\Entity\Institucion;

interface InstitucionPerfilNormalizerInterface
{
    public function normalizeConvenios(array $camposClinicos): array;

    public function normalizeInstitucion(Institucion $institucion): array;
}
