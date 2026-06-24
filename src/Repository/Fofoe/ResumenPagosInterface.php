<?php

namespace App\Repository\Fofoe;

interface ResumenPagosInterface
{
    public function resumenPagosCamposClinicos(array $filters): array;

    public function resumenPagosPosgrado(array $filters): array;

    public function resumenPagosPosgradoRotacionesParciales(array $filters): array;

    public function resumenPagosSimulacion(array $filters): array;

    public function resumePagosEscuelaEnfermeria(array $filters): array;

    public function resumenPagosEduPer(array $filters): array;
}
