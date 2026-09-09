<?php

namespace App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados;

use App\ObjectValues\SolicitudId;
use Carbon\Carbon;
use Doctrine\DBAL\Connection;

final class CamposClinicosUsingSql implements CamposClinicos
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function listaCamposClinicosAutorizados(SolicitudId $solicitudId): array
    {
        $records = $this->connection->executeQuery('
            SELECT campo_clinico.id AS id_campo_clinico,
                   monto,
                   campo_clinico.lugares_solicitados,
                   campo_clinico.lugares_autorizados,
                   count(trabajador_imss.id) AS total_trabajadores_becados,
                   fecha_inicial,
                   fecha_final,
                   campo_clinico.horario,
                   campo_clinico.asignatura,
                   unidad.nombre          AS nombre_unidad,
                   carrera.id             AS carrera_id,
                   carrera.nombre         AS nombre_carrera,
                   nivel_academico.nombre AS nombre_nivel_academico,
                   ciclo_academico.id     AS ciclo_academico_id,
                   ciclo_academico.nombre AS nombre_ciclo_academico
            FROM campo_clinico
                JOIN unidad
                    ON campo_clinico.unidad_id = unidad.id
                JOIN convenio
                    ON campo_clinico.convenio_id = convenio.id
                JOIN carrera
                    ON convenio.carrera_id = carrera.id
                LEFT JOIN nivel_academico
                    ON carrera.nivel_academico_id = nivel_academico.id
                JOIN ciclo_academico
                    ON campo_clinico.ciclo_academico_id = ciclo_academico.id
                LEFT JOIN trabajador_imss
                    ON campo_clinico.id = trabajador_imss.campo_clinico_id
            WHERE campo_clinico.solicitud_id = :solicitudId
              AND lugares_autorizados != 0
            GROUP BY
                campo_clinico.id, carrera.id,
                nivel_academico.id, ciclo_academico.id, unidad.id
        ', ['solicitudId' => $solicitudId->asInt()])->fetchAllAssociative();

        return array_map(fn(array $record) => new CampoClinico(
            $record['id_campo_clinico'],
            new Unidad($record['nombre_unidad']),
            new Convenio(
                new Carrera($record['carrera_id'], $record['nombre_carrera'], new NivelAcademico($record['nombre_nivel_academico'])),
                new CicloAcademico($record['ciclo_academico_id'], $record['nombre_ciclo_academico'])
            ),
            $record['lugares_solicitados'],
            $record['lugares_autorizados'],
            $record['total_trabajadores_becados'],
            $record['fecha_inicial'],
            $record['fecha_final'],
            $this->getNumeroSemanas($record['fecha_inicial'], $record['fecha_final']),
            $this->getMontoPagar($record['lugares_autorizados'], $record['monto']),
            $this->getEnlaceCalculoCuotas($record['id_campo_clinico'], $record['lugares_autorizados']),
            $record['horario'],
            $record['asignatura']
        ), $records);
    }

    private function getNumeroSemanas(string $fechaInicial, string $fechaFinal): int
    {
        $inicial = Carbon::instance(new \DateTime($fechaInicial));
        $final   = Carbon::instance(new \DateTime($fechaFinal));
        $dias    = 1 + $final->diffInDays($inicial, true);

        return intval($dias / 7) + ($dias % 7 > 0 ? 1 : 0);
    }

    private function getMontoPagar(int $lugaresAutorizados, mixed $monto): mixed
    {
        return $lugaresAutorizados !== 0 ? $monto : 'No aplica';
    }

    private function getEnlaceCalculoCuotas(int $id, int $lugaresAutorizados): int|string
    {
        return $lugaresAutorizados !== 0 ? $id : '';
    }
}
