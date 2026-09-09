<?php

namespace App\Repository\IE\DetalleSolicitud\TotalCamposClinicosAutorizados;

final class TotalCamposClinicosAutorizados
{
    public function __construct(
        private readonly int $total,
    ) {}

    public function getTotal(): int
    {
        return $this->total;
    }
}
