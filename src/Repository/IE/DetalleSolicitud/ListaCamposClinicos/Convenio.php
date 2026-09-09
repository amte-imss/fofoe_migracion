<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

final class Convenio
{
    public function __construct(
        private readonly Carrera $carrera,
        private readonly CicloAcademico $cicloAcademico,
    ) {}

    public function getCarrera(): Carrera { return $this->carrera; }

    public function getCicloAcademico(): CicloAcademico { return $this->cicloAcademico; }
}
