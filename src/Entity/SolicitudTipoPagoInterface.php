<?php

namespace App\Entity;

interface SolicitudTipoPagoInterface
{
    const TIPO_PAGO_MULTIPLE = 'Multiple';
    const TIPO_PAGO_UNICO = 'Único';
    const TIPO_PAGO_NULL = 'Pendiente de selección';
}
