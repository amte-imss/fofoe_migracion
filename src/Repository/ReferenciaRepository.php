<?php

namespace App\Repository;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class ReferenciaRepository implements ReferenciaRepositoryInterface
{
    private readonly Connection $connection;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    public function paginate(int $perPage = 10, int $offset = 1, array $filters = []): array
    {
        $query      = $this->createQuery($perPage, $offset, $filters);
        $queryTotal = $this->createTotalQuery($filters);

        $statement  = $this->connection->prepare($query);
        $statement2 = $this->connection->prepare($queryTotal);

        $this->bindParams($statement, $filters, $perPage, $offset);
        $this->bindParams($statement2, $filters);

        return [
            'data'  => $statement->executeQuery()->fetchAllAssociative(),
            'total' => $statement2->executeQuery()->fetchAllAssociative()[0]['total'],
        ];
    }

    public function getYears(): array
    {
        $sql = 'SELECT extract(YEAR FROM fecha_pago) AS year FROM pago WHERE fecha_pago IS NOT NULL GROUP BY 1 ORDER BY 1 DESC';

        return $this->connection->prepare($sql)->executeQuery()->fetchAllAssociative();
    }

    private function createQuery(int $perPage, int $offset, array $filters): string
    {
        $sql = "SELECT DISTINCT " .
            "pago.id id," .
            "delegacion.nombre || (CASE WHEN unidad.es_umae THEN ' / ' || unidad.nombre ELSE '' END) AS delegacion," .
            "institucion.id institucion_id," .
            "institucion.nombre institucion_nombre," .
            "solicitud.no_solicitud," .
            "(CASE WHEN solicitud.tipo_pago = 'Único' THEN solicitud.monto ELSE campo_clinico.monto END) monto," .
            "pago.referencia_bancaria," .
            "factura.id factura_id," .
            "factura.folio factura_folio," .
            "pago.fecha_pago," .
            "pago.validado," .
            "pago.factura_generada," .
            "pago.requiere_factura " .
            $this->addRelations();

        $sql  = $this->addFilters($sql, $filters);
        $sql .= $this->addOrders($filters);
        $sql .= "LIMIT :limit OFFSET :offset ";

        return $sql;
    }

    private function bindParams(\Doctrine\DBAL\Statement $statement, array $filters = [], ?int $perPage = null, ?int $offset = null): void
    {
        if (!empty($filters['institucion'])) {
            $statement->bindValue('institucion', '%' . $filters['institucion'] . '%');
        }
        if (!empty($filters['delegacion'])) {
            $statement->bindValue('delegacion', '%' . $filters['delegacion'] . '%');
        }
        if (!empty($filters['referencia'])) {
            $statement->bindValue('referencia', '%' . $filters['referencia'] . '%');
        }
        if (!empty($filters['factura'])) {
            $statement->bindValue('factura', '%' . $filters['factura'] . '%');
        }
        if (!empty($filters['no_solicitud'])) {
            $statement->bindValue('no_solicitud', '%' . $filters['no_solicitud'] . '%');
        }
        if (!empty($filters['monto']) && is_numeric($filters['monto'])) {
            $statement->bindValue('monto', '%' . $filters['monto'] . '%');
        }

        $year   = $filters['year'] ?? Carbon::now()->format('Y');
        $statement->bindValue('fecha_i', "{$year}-01-01");
        $statement->bindValue('fecha_f', "{$year}-12-31");

        if ($perPage && is_numeric($perPage)) {
            $statement->bindValue('limit', $perPage);
            if ($offset && is_numeric($offset)) {
                $statement->bindValue('offset', ($offset - 1) * $perPage);
            }
        }
    }

    private function addFilters(string $sql, array $filters): string
    {
        $sql .= " WHERE pago.fecha_pago IS NOT NULL ";
        $sql .= " AND pago.is_test = false ";
        $sql .= " AND (solicitud.is_test = false OR solicitud.is_test IS NULL) ";

        if (!empty($filters['institucion'])) {
            $sql .= ' AND upper(unaccent(institucion.nombre)) like UPPER(unaccent(:institucion))';
        }
        if (!empty($filters['delegacion'])) {
            $sql .= ' AND (upper(unaccent(delegacion.nombre)) like UPPER(unaccent(:delegacion))'
                . ' OR (unidad.es_umae AND upper(unaccent(unidad.nombre)) like UPPER(unaccent(:delegacion))))';
        }
        if (!empty($filters['referencia'])) {
            $sql .= ' AND upper(unaccent(pago.referencia_bancaria)) like UPPER(unaccent(:referencia))';
        }
        if (!empty($filters['factura'])) {
            $sql .= ' AND upper(unaccent(factura.folio)) like UPPER(unaccent(:factura))';
        }
        if (!empty($filters['no_solicitud'])) {
            $sql .= ' AND upper(unaccent(solicitud.no_solicitud)) like UPPER(unaccent(:no_solicitud))';
        }
        if (!empty($filters['monto']) && is_numeric($filters['monto'])) {
            $sql .= " AND ((solicitud.tipo_pago = 'Único' AND concat(solicitud.monto,'') like :monto)"
                . " OR (solicitud.tipo_pago = 'Multiple' AND concat(campo_clinico.monto,'') like :monto))";
        }
        if (!empty($filters['estado'])) {
            $sql .= match ($filters['estado']) {
                'a' => ' AND pago.validado is null',
                'b' => ' AND pago.validado = true AND ((pago.requiere_factura = true AND factura.id IS NOT NULL) OR (pago.requiere_factura = false))',
                'c' => ' AND pago.validado = true AND pago.requiere_factura = true AND factura.id IS NULL',
                'd' => ' AND pago.validado = false',
                default => '',
            };
        }

        $sql .= ' AND pago.fecha_pago >= :fecha_i AND pago.fecha_pago <= :fecha_f';

        return $sql;
    }

    private function createTotalQuery(array $filters): string
    {
        $sql = "SELECT count(distinct pago.id) total " . $this->addRelations();
        return $this->addFilters($sql, $filters);
    }

    private function addRelations(): string
    {
        return "FROM pago " .
            "INNER JOIN referencias ON referencias.referencia_bancaria = pago.referencia_bancaria " .
            "INNER JOIN solicitud ON pago.solicitud_id = solicitud.id " .
            "INNER JOIN campo_clinico ON solicitud.id = campo_clinico.solicitud_id " .
            "AND (solicitud.tipo_pago = 'Único' OR campo_clinico.referencia_bancaria = pago.referencia_bancaria) " .
            "INNER JOIN unidad ON campo_clinico.unidad_id = unidad.id " .
            "INNER JOIN convenio ON campo_clinico.convenio_id = convenio.id " .
            "INNER JOIN delegacion ON unidad.delegacion_id = delegacion.id " .
            "INNER JOIN institucion ON convenio.institucion_id = institucion.id " .
            "LEFT JOIN factura ON pago.factura_id = factura.id ";
    }

    private function addOrders(array $filters): string
    {
        $sql = ' ORDER BY ';
        $sql .= match ($filters['orderby'] ?? null) {
            'a'     => '9 DESC ',
            'b'     => '9 ASC ',
            'c'     => 'solicitud.no_solicitud DESC ',
            default => 'solicitud.no_solicitud ASC ',
        };
        $sql .= ', 1 DESC ';

        return $sql;
    }
}
