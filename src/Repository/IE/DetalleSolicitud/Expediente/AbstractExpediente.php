<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

abstract class AbstractExpediente implements Expediente
{
    protected function getDescripcion(array $record): string
    {
        return sprintf('Monto: $%s', number_format($record['monto'], 2));
    }

    protected function createFormatosFofoe(string $estatus): ?FormatosFofoe
    {
        $estatusParaMostrarFormatosFofoe = [
            'Montos validados CAME',
            'Formatos de pago generados',
            'Cargando comprobantes',
            'En validación FOFOE',
            'Credenciales generadas',
        ];

        if (!in_array($estatus, $estatusParaMostrarFormatosFofoe, strict: true)) {
            return null;
        }

        return new FormatosFofoe(null, 'Formatos de pago FOFOE', '');
    }
}
