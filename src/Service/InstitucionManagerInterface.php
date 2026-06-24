<?php

namespace App\Service;

use App\Entity\Institucion;

interface InstitucionManagerInterface
{
    public function update(Institucion $institucion): void;
}
