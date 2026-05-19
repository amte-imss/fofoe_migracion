<?php

namespace App\Entity\Posgrado;


interface ResidenciaInterface
{
    const NUEVO = 'Residencia registrada';
    const FORMATO_PAGO_DESCARGADO = 'Formato de Pago descargado';
    const EN_VALIDACION_FOFOE = 'En validación FOFOE';
    const PAGO_NO_VALIDO = 'Pago no válido';
    const PENDIENTE_FACTURA_FOFOE = 'Pendiente factura FOFOE';
    const PAGO_VALIDADO_FOFOE = 'Pago validado FOFOE';

    const TIPO_EXTRANJERO_IMSS = 'EXTRANJERO_IMSS';
    const TIPO_EXTRANJERO_NO_IMSS = 'EXTRANJERO_NO_IMSS';
}
