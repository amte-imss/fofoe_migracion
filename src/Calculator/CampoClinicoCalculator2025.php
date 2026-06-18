<?php

namespace App\Calculator;

use App\Entity\CampoClinico;
use App\Entity\DescuentoMonto;

class CampoClinicoCalculator2025
{
    public function getDetail(CampoClinico $campoClinico): array
    {
        $montoCarrera  = $campoClinico->getMontoCarrera();
        $descuentos    = $campoClinico->getDescuentos();
        $lugares       = $campoClinico->getLugaresAutorizados();
        $inscripcion   = $montoCarrera->getMontoInscripcion();
        $colegiatura   = $montoCarrera->getMontoColegiatura();
        $esCCS         = $campoClinico->getCicloAcademico()->getId() === 1;
        $factorSemanal = $esCCS ? 0.005 : 0.50;
        $numSemanas    = $campoClinico->getWeeks();

        $total              = 0.0;
        $detail             = [];
        $lugaresSinDescuento = $lugares;

        /** @var DescuentoMonto $descuento */
        foreach ($descuentos as $descuento) {
            $inscripcionConDescuento = $inscripcion * (1 - $descuento->getDescuentoInscripcion() / 100);
            $colegiaturaConDescuento = $colegiatura * (1 - $descuento->getDescuentoColegiatura() / 100);
            $inscripcionColegiatura  = $inscripcionConDescuento + $colegiaturaConDescuento;
            $importeAlumno           = $inscripcionColegiatura * $factorSemanal;
            $numAlumnos              = $descuento->getNumAlumnos();
            $subTotal                = $importeAlumno * $numAlumnos;
            $subtotal2               = $esCCS ? $subTotal * $numSemanas : $subTotal;

            $detail[] = [
                'porcentaje_inscripcion'  => $descuento->getDescuentoInscripcion(),
                'porcentaje_colegiatura'  => $descuento->getDescuentoColegiatura(),
                'inscripcion_descuento'   => $inscripcionConDescuento,
                'colegiatura_descuento'   => $colegiaturaConDescuento,
                'inscripcion_colegiatura' => $inscripcionColegiatura,
                'factor_semanal'          => $factorSemanal,
                'importe_alumno'          => $importeAlumno,
                'num_alumnos'             => $numAlumnos,
                'sub_semanas'             => $numSemanas,
                'subtotal'                => $subTotal,
                'subtotal2'               => $subtotal2,
            ];

            $total              += $subtotal2;
            $lugaresSinDescuento -= $numAlumnos;
        }

        $detailSinDescuento = null;
        if ($lugaresSinDescuento > 0) {
            $inscripcionColegiatura = $inscripcion + $colegiatura;
            $importeAlumno          = $inscripcionColegiatura * $factorSemanal;
            $subTotal               = $importeAlumno * $lugaresSinDescuento;
            $subtotal2              = $esCCS ? $subTotal * $numSemanas : $subTotal;

            $detailSinDescuento = [
                'inscripcion'             => $inscripcion,
                'colegiatura'             => $colegiatura,
                'inscripcion_colegiatura' => $inscripcionColegiatura,
                'factor_semanal'          => $factorSemanal,
                'importe_alumno'          => $importeAlumno,
                'num_alumnos'             => $lugaresSinDescuento,
                'sub_semanas'             => $numSemanas,
                'subtotal'                => $subTotal,
                'subtotal2'               => $subtotal2,
            ];

            $total += $subtotal2;
        }

        return [
            'detail_descuentos'    => $detail,
            'detail_sin_descuento' => $detailSinDescuento,
            'total'                => $total,
        ];
    }
}
