<?php

namespace App\Calculator;

use App\Entity\CampoClinico;
use App\Entity\DescuentoMonto;
use App\Entity\MontoCarrera;
use App\Entity\Solicitud;

class CampoClinicoCalculator implements CampoClinicoCalculatorInterface
{
    public function getMontoAPagar(CampoClinico $campo, Solicitud $solicitud): float
    {
        /** @var MontoCarrera $monto */
        $monto              = $campo->getMontoCarrera();
        $descuentos         = $monto ? $monto->getDescuentos() : [];
        $idCicloAcademico   = $campo->getCicloAcademico()->getId();
        $numSemanas         = $idCicloAcademico === 1 ? $campo->getWeeks() : 1;

        $totalAlumnosCobrados = $campo->getTotalTrabajadoresBecados();
        $montoTotal           = 0.0;

        /** @var DescuentoMonto $descuento */
        foreach ($descuentos as $descuento) {
            $alumnosDescuento = min(
                $campo->getLugaresAutorizados() - $totalAlumnosCobrados,
                $descuento->getNumAlumnos()
            );

            $subTotal1   = $this->getSubtotalCAI($monto, $descuento);
            $subTotal2   = $subTotal1 * ($idCicloAcademico === 1 ? 0.005 : 0.50);
            $montoTotal += $alumnosDescuento * $subTotal2 * $numSemanas;

            $totalAlumnosCobrados += $alumnosDescuento;
        }

        $alumnosSinDescuento = max($campo->getLugaresAutorizados() - $totalAlumnosCobrados, 0);
        $subTotal1           = $this->getSubtotalCAI($monto);
        $subTotal2           = $subTotal1 * ($idCicloAcademico === 1 ? 0.005 : 0.50);
        $montoTotal         += $alumnosSinDescuento * $subTotal2 * $numSemanas;

        return $montoTotal;
    }

    private function getSubtotalCAI(MontoCarrera $montoCarrera, ?DescuentoMonto $descuento = null): float
    {
        $descIns = $descuento ? $this->validaPorcentaje($descuento->getDescuentoInscripcion()) : 0;
        $descCol = $descuento ? $this->validaPorcentaje($descuento->getDescuentoColegiatura()) : 0;

        return $montoCarrera->getMontoInscripcion() * ((100 - $descIns) / 100.0)
            + $montoCarrera->getMontoColegiatura() * ((100 - $descCol) / 100.0);
    }

    private function validaPorcentaje(int $monto): float
    {
        if ($monto < 0)   return 0.0;
        if ($monto > 100) return 100.0;
        return (float) $monto;
    }
}
