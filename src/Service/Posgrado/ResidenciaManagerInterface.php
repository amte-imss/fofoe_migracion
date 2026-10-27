<?php

namespace App\Service\Posgrado;

use App\Entity\Posgrado\Residencia;
use App\Entity\Usuario;

interface ResidenciaManagerInterface
{
    /**
     * @return array{0: list<list<mixed>>, 1: list<mixed>, status: bool, errores?: list<string>}
     */
    public function processDataFromXlsx(string $filenameXlsx, mixed $ciclo, Usuario $usuario): array;

    /**
     * @return array{data: array<string, mixed>, errores: list<string>, status: bool}
     */
    public function processDatoUnitario(array $data, Usuario $usuario): array;

    public function actualizarResidenciaExImss(Residencia $residencia): void;
}
