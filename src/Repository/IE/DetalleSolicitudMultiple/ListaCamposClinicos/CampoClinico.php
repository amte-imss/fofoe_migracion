<?php

namespace App\Repository\IE\DetalleSolicitudMultiple\ListaCamposClinicos;

use App\Repository\IE\DetalleSolicitud\ListaCamposClinicos\CampoClinico as CampoClinicoBase;
use App\Repository\IE\DetalleSolicitud\ListaCamposClinicos\Convenio;
use App\Repository\IE\DetalleSolicitud\ListaCamposClinicos\Unidad;

final class CampoClinico extends CampoClinicoBase
{
    public function __construct(
        int     $id,
        Convenio $convenio,
        int     $lugaresSolicitados,
        int     $lugaresAutorizados,
        int     $totalTrabajadoresBecados,
        string  $fechaInicial,
        string  $fechaFinal,
        Unidad  $unidad,
        int     $noSemanas,
        ?string $obsValRegistro,
        ?string $horario,
        ?string $asignatura,
        private readonly Pago    $pago,
        private readonly string  $estatus,
        private readonly ?string $referenciaBancaria,
    ) {
        parent::__construct(
            $id, $convenio, $lugaresSolicitados, $lugaresAutorizados,
            $totalTrabajadoresBecados, $fechaInicial, $fechaFinal,
            $unidad, $noSemanas, $obsValRegistro, $horario, $asignatura
        );
    }

    public function getPago(): Pago { return $this->pago; }

    public function getEstatus(): string { return $this->estatus; }

    public function getReferenciaBancaria(): ?string { return $this->referenciaBancaria; }
}
