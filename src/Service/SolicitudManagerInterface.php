<?php

namespace App\Service;

use App\Entity\Solicitud;
use App\Entity\Usuario;

interface SolicitudManagerInterface
{
    public function update(Solicitud $solicitud): mixed;

    public function create(Solicitud $solicitud): mixed;

    public function finalizar(Solicitud $solicitud, ?Usuario $came_usuario = null): void;

    public function validarRegistroSolicitud(Solicitud $solicitud, ?Usuario $came_usuario = null): array;

    public function registrarMontos(Solicitud $solicitud, array $originalDescuentos = []): void;

    public function validarMontos(
        Solicitud $solicitud,
        array $montos,
        bool $is_valid,
        ?Usuario $came_usuario = null,
        array $originalDescuentos = [],
    ): mixed;

    public function generateUser(Solicitud $solicitud, ?Usuario $came_usuario = null): mixed;
}
