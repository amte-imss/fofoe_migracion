<?php

namespace App\Repository\Fofoe\Posgrado;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final class ReferenciaRepository implements ReferenciaRepositoryInterface
{
    private const string SELECT_COLUMNS = '
        SELECT
            pago.id AS id,
            posgrado_residencia.tipo_delegacion_umae,
            posgrado_residencia.delegacion,
            posgrado_residencia.id AS residencia_id,
            usuario.nombre AS residente_nombre,
            usuario.apellido_paterno AS residente_apellido_paterno,
            usuario.apellido_materno AS residente_apellido_materno,
            posgrado_residencia.folio,
            posgrado_residencia.monto AS monto,
            posgrado_residencia.tipo_moneda AS tipo_moneda,
            posgrado_residencia.tasa_cambio AS tasa_cambio,
            pago.referencia_bancaria,
            factura.id AS factura_id,
            factura.folio AS factura_folio,
            pago.fecha_pago,
            pago.validado,
            pago.factura_generada,
            pago.requiere_factura
    ';

    private const string FROM = '
        FROM pago
            JOIN posgrado_residencia ON pago.posgrado_residencia_id = posgrado_residencia.id
            JOIN posgrado_residente ON posgrado_residencia.residente_id = posgrado_residente.id
            JOIN usuario ON posgrado_residente.usuario_id = usuario.id
            LEFT JOIN factura ON pago.factura_id = factura.id
    ';

    /**
     * Filters matched with LIKE '%value%'. MariaDB's default *_ci collations
     * are already case- and accent-insensitive.
     */
    private const array LIKE_FILTERS = [
        'residente' => "CONCAT_WS(' ', usuario.nombre, usuario.apellido_paterno, usuario.apellido_materno)",
        'delegacion' => 'posgrado_residencia.delegacion',
        'referencia' => 'pago.referencia_bancaria',
        'factura' => 'factura.folio',
        'folio' => 'posgrado_residencia.folio',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function paginate(int $perPage = 10, int $page = 1, array $filters = []): array
    {
        $perPage = max(1, $perPage);
        $page = max(1, $page);

        [$where, $params] = $this->buildWhere($filters);

        $data = $this->connection->executeQuery(
            self::SELECT_COLUMNS . self::FROM . $where . $this->buildOrderBy($filters) . ' LIMIT :limit OFFSET :offset',
            [...$params, 'limit' => $perPage, 'offset' => ($page - 1) * $perPage],
            ['limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER],
        )->fetchAllAssociative();

        $total = (int) $this->connection->executeQuery(
            'SELECT COUNT(DISTINCT pago.id)' . self::FROM . $where,
            $params,
        )->fetchOne();

        return ['data' => $data, 'total' => $total];
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function buildWhere(array $filters): array
    {
        $year = $this->filterValue($filters, 'year');
        $year = $year !== null && ctype_digit($year) ? (int) $year : (int) date('Y');

        $conditions = [
            'pago.fecha_pago IS NOT NULL',
            'pago.fecha_pago >= :fecha_i',
            'pago.fecha_pago < :fecha_f',
        ];
        $params = [
            'fecha_i' => sprintf('%d-01-01', $year),
            'fecha_f' => sprintf('%d-01-01', $year + 1),
        ];

        foreach (self::LIKE_FILTERS as $key => $column) {
            $value = $this->filterValue($filters, $key);

            if ($value !== null) {
                $conditions[] = sprintf('%s LIKE :%s', $column, $key);
                $params[$key] = '%' . $value . '%';
            }
        }

        $tipo = $this->filterValue($filters, 'tipo');
        if ($tipo !== null) {
            $conditions[] = 'posgrado_residencia.tipo = :tipo';
            $params['tipo'] = $tipo === 'imss' ? 'EXTRANJERO_IMSS' : 'EXTRANJERO_NO_IMSS';
        }

        $monto = $this->filterValue($filters, 'monto');
        if ($monto !== null && is_numeric($monto)) {
            $conditions[] = 'posgrado_residencia.monto LIKE :monto';
            $params['monto'] = '%' . $monto . '%';
        }

        $estadoCondition = match ($this->filterValue($filters, 'estado')) {
            'pendiente_val' => 'pago.validado IS NULL',
            'validado_facturado' => 'pago.validado = 1 AND ((pago.requiere_factura = 1 AND factura.id IS NOT NULL) OR pago.requiere_factura = 0)',
            'pendiente_factura' => 'pago.validado = 1 AND pago.requiere_factura = 1 AND factura.id IS NULL',
            'no_valido' => 'pago.validado = 0',
            default => null,
        };

        if ($estadoCondition !== null) {
            $conditions[] = '(' . $estadoCondition . ')';
        }

        return [' WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function buildOrderBy(array $filters): string
    {
        $orderBy = match ($this->filterValue($filters, 'orderby')) {
            null => 'posgrado_residencia.monto DESC',
            'fecha_pago_desc' => 'pago.fecha_pago DESC',
            'fecha_pago_asc' => 'pago.fecha_pago ASC',
            'folio_desc' => 'pago.referencia_bancaria DESC',
            default => 'pago.referencia_bancaria ASC',
        };

        return sprintf(' ORDER BY %s, pago.id DESC', $orderBy);
    }

    /**
     * Returns the filter as a non-empty string, or null when missing, empty or not scalar.
     */
    private function filterValue(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;

        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }
}
