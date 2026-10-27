<?php

namespace App\Repository\Fofoe;

use Carbon\Carbon;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;

final class ResumenPagos implements ResumenPagosInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function resumenPagosCamposClinicos($filters): array
    {
        return ['data' => $this->queryResumenCamposClinicos($filters)];
    }

    public function resumenPagosPosgrado($filters): array
    {
        return ['data' => $this->queryResumenPosgrado($filters)];
    }

    public function resumenPagosPosgradoRotacionesParciales($filters): array
    {
        return ['data' => $this->queryResumenPosgradoExtNoIMSS($filters)];
    }

    public function resumenPagosSimulacion($filters): array
    {
        return ['data' => $this->makeQuery($filters, 'SIMULACION')];
    }

    public function resumePagosEscuelaEnfermeria($filters): array
    {
        return [
            'data' => $this->queryResumenEscuelasEnfermeria($filters)
        ];
    }

    public function resumenPagosEduPer($filters): array
    {
        return [
            'data' => $this->queryResumenEduPer($filters)
        ];
    }

    private function makeQuery($filters, $tipoQuery): array
    {
        $filters['tipoQuery'] = $tipoQuery;
        $query = $this->createQuery($filters);
        $statement = $this->entityManager->getConnection()->prepare($query);
        $this->bindParams($statement, $filters);
        $result = $statement->executeQuery()->fetchAllAssociative();

        if ($result) {
            $result = $result[0];
        } else {
            $result = [];
            $result['pendientes_val'] = 0;
            $result['pendientes_facturacion'] = 0;
            $result['no_validos'] = 0;
            $result['validados_facturados'] = 0;
        }

        return $result;
    }

    private function createQuery($filters): string
    {
        $joinSql = $this->getJoinSql($filters);
        $whereCondition = $this->getWhereCondition($filters);
        $groupByCondition = $this->getGroupByCondition($filters);
        $query = '
        SELECT
            sum(
                    (CASE WHEN p.fecha_pago is not null AND p.validado is null THEN 1
                        ELSE 0
                        END)
                ) as pendientes_val,
            sum(
                    (CASE WHEN p.fecha_pago is not null AND p.validado=True
                        AND ( p.requiere_factura=True AND p.factura_id is null)
                        THEN 1
                        ELSE 0
                        END)
                ) as pendientes_facturacion,
            sum(
                    (CASE WHEN p.fecha_pago is not null AND p.validado=False
                        THEN 1
                        ELSE 0
                        END)
                ) as no_validos,
            sum(
                    (CASE WHEN p.validado=True
                        AND ( requiere_factura=False OR (requiere_factura=true
                            AND p.factura_id is not null))
                        THEN 1
                        ELSE 0
                        END)
                ) as validados_facturados

            FROM
            (
                SELECT *
                FROM (
                    SELECT fecha_pago,id, solicitud_id,posgrado_residencia_id, simulacion_solicitud_id,
                           referencia_bancaria, validado ,requiere_factura, factura_id, escuela_enf_alumno_id,
                           ROW_NUMBER() OVER (PARTITION BY referencia_bancaria ORDER BY id desc) AS fila_numero
                    FROM pago
                ) subconsulta
                WHERE fila_numero = 1
            ) p
            '
            . $joinSql .
            '
        WHERE '
            . $whereCondition .
            ' GROUP BY '
            . $groupByCondition;
        return $query;
    }

    private function bindParams(Statement $statement, $filters = []): void
    {
        if (array_key_exists('tipo', $filters)) {
            $statement->bindValue('tipo', $filters['tipo']);
        }

        if (empty($filters['year'])) {
            $filters['year'] = Carbon::now()->format('Y');
        }
        $fecha_i = "{$filters['year']}-01-01";
        $fecha_f = "{$filters['year']}-12-31";
        $statement->bindValue('fecha_i', $fecha_i);
        $statement->bindValue('fecha_f', $fecha_f);
    }

    private function getJoinSql($filters): string
    {
        $joinSql = '';

        if (array_key_exists('tipo', $filters)) {
            $joinSql .= match ($filters['tipo']) {
                'EXTRANJERO_NO_IMSS', 'EXTRANJERO_IMSS' => '
                        join posgrado_residencia pr on p.posgrado_residencia_id = pr.id
                            AND pr.tipo = :tipo
                    ',
                default => '',
            };
        }

        return $joinSql;
    }

    private function getWhereCondition($filters): string
    {
        $whereCondition = match ($filters['tipoQuery']) {
            'CC' => ' p.solicitud_id is not null ',
            'POSGRADO_RP', 'POSGRADO' => ' p.posgrado_residencia_id is not null ',
            'SIMULACION' => ' p.simulacion_solicitud_id is not null',
            'ESCUELA_ENFERMERIA' => ' p.escuela_enf_alumno_id is not null',
            default => ' ',
        };

        if (array_key_exists('year', $filters)) {
            $whereCondition .= ' AND p.fecha_pago >= :fecha_i AND p.fecha_pago <= :fecha_f ';
        }

        return $whereCondition;
    }

    private function getGroupByCondition($filters): string
    {
        return match ($filters['tipoQuery']) {
            'CC' => ' solicitud_id is not null ',
            'POSGRADO_RP', 'POSGRADO' => ' posgrado_residencia_id is not null ',
            'SIMULACION' => ' simulacion_solicitud_id is not null ',
            'ESCUELA_ENFERMERIA' => ' escuela_enf_alumno_id is not null ',
            default => ' ',
        };
    }

    public function queryResumenCamposClinicos($filters): array
    {
        return $this->fetchResumenView('v_estatus_pago_cc');
    }

    public function queryResumenEscuelasEnfermeria($filters): array
    {
        return $this->fetchResumenView('v_estatus_pago_ef');
    }

    public function queryResumenPosgradoExtNoIMSS($filters): array
    {
        return $this->fetchResumenView('v_estatus_pago_penoimss');
    }

    public function queryResumenPosgrado($filters): array
    {
        return $this->fetchResumenView('v_estatus_pago_residentes');
    }

    public function queryResumenEduPer($filters): array
    {
        return $this->fetchResumenView('v_estatus_pago_eduper_' . $filters['tipo']);
    }

    private function fetchResumenView(string $viewName): array
    {
        $query = 'select * from ' . $viewName;
        $statement = $this->entityManager->getConnection()->prepare($query);
        $result = $statement->executeQuery()->fetchAllAssociative();

        if ($result) {
            $result = $result[0];
        } else {
            $result = [];
            $result['pendientes_val'] = 0;
            $result['pendientes_facturacion'] = 0;
            $result['no_validos'] = 0;
            $result['validados_facturados'] = 0;
        }

        return $result;
    }
}
