<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouldNotFindSolicitud extends NotFoundHttpException
{
    public static function withId($id)
    {
        return new self(sprintf('La solicitud con id [%s] no existe.', $id));
    }
}
