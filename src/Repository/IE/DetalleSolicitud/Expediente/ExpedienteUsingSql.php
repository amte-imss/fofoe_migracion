<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

use App\ObjectValues\SolicitudId;
use Doctrine\DBAL\Connection;

final class ExpedienteUsingSql extends AbstractExpediente implements Expediente
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function expedienteBySolicitud(SolicitudId $solicitudId): Documents
    {
        return new Documents(
            $this->getOficioMonto($solicitudId),
            $this->getComprobantesPago($solicitudId),
            $this->getFacturas($solicitudId),
            $this->getFormatosFofoe($solicitudId)
        );
    }

    private function getOficioMonto(SolicitudId $solicitudId): OficioMontos
    {
        $oficioRecord = $this->connection->executeQuery('
            SELECT documento,
                   fecha_comprobante,
                   url_archivo
            FROM solicitud
            WHERE solicitud.id = :id
        ', ['id' => $solicitudId->asInt()])->fetchAssociative();

        $montosCarreraRecord = $this->connection->executeQuery('
            SELECT nivel_academico.nombre AS nombre_nivel_academico,
                   carrera.nombre AS nombre_carrera,
                   monto_inscripcion,
                   monto_colegiatura,
                   campo_clinico.fecha_inicial,
                   campo_clinico.fecha_final,
                   unidad.nombre AS nombre_unidad,
                   monto_carrera.id AS monto_carrera_id
            FROM solicitud
            JOIN campo_clinico
              ON solicitud.id = campo_clinico.solicitud_id
            JOIN unidad
              ON campo_clinico.unidad_id = unidad.id
            JOIN monto_carrera
              ON campo_clinico.id = monto_carrera.campo_clinico_id
            JOIN carrera
              ON monto_carrera.carrera_id = carrera.id
            LEFT JOIN nivel_academico
              ON carrera.nivel_academico_id = nivel_academico.id
            WHERE solicitud.id = :id
        ', ['id' => $solicitudId->asInt()])->fetchAllAssociative();

        return new OficioMontos(
            $oficioRecord['fecha_comprobante'],
            $this->getDescripcionOficioMontos($montosCarreraRecord),
            $oficioRecord['url_archivo']
        );
    }

    private function getDescripcionOficioMontos(array $montosCarreraRecord): string
    {
        $items = array_map(fn(array $record) => sprintf(
            "%s %s: Inscripción $%s, Colegiatura: $%s\n%s, %s \n%s",
            $record['nombre_nivel_academico'],
            $record['nombre_carrera'],
            number_format($record['monto_inscripcion'], 2),
            number_format($record['monto_colegiatura'], 2),
            (new \DateTime($record['fecha_inicial']))->format('d/m/Y') . '-' . (new \DateTime($record['fecha_final']))->format('d/m/Y'),
            $record['nombre_unidad'],
            $this->getDescuentos($record['monto_carrera_id'])
        ), $montosCarreraRecord);

        return implode("\n --------- \n", $items);
    }

    private function getDescuentos(int $monto_carrera_id): string
    {
        $descuentosRecord = $this->connection->executeQuery('
            SELECT num_alumnos,
                   descuento_inscripcion,
                   descuento_colegiatura
            FROM descuento_monto
            WHERE monto_carrera_id = :id
        ', ['id' => $monto_carrera_id])->fetchAllAssociative();

        $textHeaderResult = count($descuentosRecord) > 0 ? "Descuentos: \n" : '';

        $itemsDesc = array_map(fn(array $record) => sprintf(
            " %s alumno(s) con descuento de %s\n",
            $record['num_alumnos'],
            (($descInsc = is_numeric($record['descuento_inscripcion']) && $record['descuento_inscripcion'] != '0' ? (float) $record['descuento_inscripcion'] : 0) > 0 ? $descInsc . '% inscripción, ' : '') .
            (($descCol  = is_numeric($record['descuento_colegiatura'])  && $record['descuento_colegiatura']  != '0' ? (float) $record['descuento_colegiatura']  : 0) > 0 ? $descCol  . '% colegiatura'  : '')
        ), $descuentosRecord);

        return $textHeaderResult . implode(' ', $itemsDesc);
    }

    private function getComprobantesPago(SolicitudId $solicitudId): array
    {
        $records = $this->connection->executeQuery('
            SELECT pago.comprobante_pago,
                   pago.fecha_creacion,
                   pago.monto,
                   solicitud.referencia_bancaria,
                   pago.requiere_factura,
                   pago.id AS pago_id
            FROM solicitud
            JOIN pago
              ON solicitud.id = pago.solicitud_id
            WHERE solicitud.id = :id
              AND solicitud.referencia_bancaria = pago.referencia_bancaria
              AND pago.fecha_pago IS NOT NULL
        ', ['id' => $solicitudId->asInt()])->fetchAllAssociative();

        return array_map(fn(array $record) => new ComprobantePago(
            $record['fecha_creacion'],
            $this->getDescripcion($record),
            $record['comprobante_pago'],
            [
                'pagoId'            => $record['pago_id'],
                'referenciaBancaria' => $record['referencia_bancaria'],
            ]
        ), $records);
    }

    private function getFacturas(SolicitudId $solicitudId): array
    {
        $records = $this->connection->executeQuery('
            SELECT fecha_facturacion,
                   zip,
                   folio,
                   factura.monto AS factura_monto,
                   factura.id AS factura_id
            FROM solicitud
              JOIN pago
                ON solicitud.id = pago.solicitud_id
              JOIN factura
                ON pago.factura_id = factura.id
            WHERE solicitud.id = :id
              AND solicitud.referencia_bancaria = pago.referencia_bancaria
        ', ['id' => $solicitudId->asInt()])->fetchAllAssociative();

        return array_map(fn(array $record) => new Factura(
            $record['fecha_facturacion'],
            sprintf('Folio: %s, Monto: $%s', $record['folio'], number_format($record['factura_monto'], 2)),
            $record['zip'],
            ['facturaId' => $record['factura_id']]
        ), $records);
    }

    private function getFormatosFofoe(SolicitudId $solicitudId): ?FormatosFofoe
    {
        $estatus = $this->connection->executeQuery('
            SELECT estatus
            FROM solicitud
            WHERE solicitud.id = :id
        ', ['id' => $solicitudId->asInt()])->fetchOne();

        return $this->createFormatosFofoe($estatus);
    }
}
