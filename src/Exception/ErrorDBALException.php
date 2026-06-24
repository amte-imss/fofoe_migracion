<?php

namespace App\Exception;


use App\ObjectValues\SolicitudId;
use Doctrine\DBAL\Exception as DBALException;

final class ErrorDBALException extends \RuntimeException implements DBALException
{
    public static function withExistSolicitud(SolicitudId $solicitudId)
    {
        return new self(sprintf('Hubo un error con la solicitud con id: %id', $solicitudId->asInt()));
    }
}
