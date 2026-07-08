<?php

namespace App\Repository\IE\DetalleSolicitud\TotalCamposClinicosAutorizados;

use App\ObjectValues\SolicitudId;
use Doctrine\DBAL\Connection;

class TotalCamposClinicosUsingSql implements TotalCamposClinicos
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function totalCamposClinicosAutorizados(SolicitudId $solicitudId): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(campo_clinico.id) AS total
             FROM campo_clinico
             WHERE campo_clinico.solicitud_id = :id
             AND campo_clinico.lugares_autorizados > 0',
            ['id' => $solicitudId->asInt()]
        )->fetchOne();
    }
}
