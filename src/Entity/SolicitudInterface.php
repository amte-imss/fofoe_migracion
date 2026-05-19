<?php

namespace App\Entity;


interface SolicitudInterface
{
    const CREADA = 'Solicitud creada';
    const REGISTRADA = 'Solicitud registrada';
    const CONFIRMADA = 'Solicitud confirmada';
    const EN_VALIDACION_DE_MONTOS_CAME = 'En validación de montos CAME';
    const MONTOS_INCORRECTOS_CAME = 'Montos incorrectos CAME';
    const MONTOS_VALIDADOS_CAME = 'Montos validados CAME';
    const FORMATOS_DE_PAGO_GENERADOS = 'Formatos de pago generados';
    const CARGANDO_COMPROBANTES = 'Cargando comprobantes';
    const EN_VALIDACION_FOFOE = 'En validación FOFOE';
    const CREDENCIALES_GENERADAS = 'Credenciales generadas';
}
