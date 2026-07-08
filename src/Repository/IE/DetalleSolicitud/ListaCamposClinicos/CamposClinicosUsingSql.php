<?php

namespace App\Repository\IE\DetalleSolicitud\ListaCamposClinicos;

use App\ObjectValues\SolicitudId;
use Carbon\Carbon;
use Doctrine\DBAL\Connection;

final class CamposClinicosUsingSql implements CamposClinicos
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function listaCamposClinicosBySolicitud(SolicitudId $solicitudId): array
    {
        $records = $this->connection->executeQuery('
            SELECT campo_clinico.id AS id_campo_clinico,
                   campo_clinico.lugares_solicitados,
                   campo_clinico.lugares_autorizados,
                   campo_clinico.obs_val_registro,
                   campo_clinico.horario,
                   campo_clinico.asignatura,
                   count(trabajador_imss.id) AS total_trabajadores_becados,
                   campo_clinico.fecha_inicial,
                   campo_clinico.fecha_final,
                   carrera.nombre AS nombre_carrera,
                   nivel_academico.nombre AS nombre_nivel_academico,
                   ciclo_academico.nombre AS nombre_ciclo_academico,
                   unidad.nombre AS nombre_unidad,
                   unidad.es_umae,
                   delegacion.nombre AS nombre_delegacion
            FROM campo_clinico
              JOIN solicitud
                ON campo_clinico.solicitud_id = solicitud.id
              JOIN convenio
                ON campo_clinico.convenio_id = convenio.id
              JOIN carrera
                ON convenio.carrera_id = carrera.id
              LEFT JOIN nivel_academico
                ON carrera.nivel_academico_id = nivel_academico.id
              JOIN ciclo_academico
                ON campo_clinico.ciclo_academico_id = ciclo_academico.id
              JOIN unidad
                ON campo_clinico.unidad_id = unidad.id
              JOIN delegacion
                ON unidad.delegacion_id = delegacion.id
              LEFT JOIN trabajador_imss
                ON campo_clinico.id = trabajador_imss.campo_clinico_id
            WHERE solicitud.id = :id
            GROUP BY
                campo_clinico.id, carrera.id,
                nivel_academico.id, ciclo_academico.id, unidad.id, delegacion.id
        ', ['id' => $solicitudId->asInt()])
            ->fetchAllAssociative();

        return array_map(function (array $record): CampoClinico {
            return new CampoClinico(
                $record['id_campo_clinico'],
                new Convenio(
                    new Carrera($record['nombre_carrera'], new NivelAcademico($record['nombre_nivel_academico'])),
                    new CicloAcademico($record['nombre_ciclo_academico'])
                ),
                $record['lugares_solicitados'],
                $record['lugares_autorizados'],
                $record['total_trabajadores_becados'],
                $record['fecha_inicial'],
                $record['fecha_final'],
                new Unidad($record['nombre_unidad'], $record['es_umae'], $record['nombre_delegacion']),
                $this->getNoSemanas($record['fecha_inicial'], $record['fecha_final']),
                $record['obs_val_registro'],
                $record['horario'],
                $record['asignatura']
            );
        }, $records);
    }

    private function getNoSemanas(string $fechaInicial, string $fechaFinal): int
    {
        $inicial = Carbon::instance(new \DateTime($fechaInicial));
        $final   = Carbon::instance(new \DateTime($fechaFinal));
        $dias    = 1 + $final->diffInDays($inicial, true);

        return intval($dias / 7) + ($dias % 7 > 0 ? 1 : 0);
    }
}
