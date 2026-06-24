<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouldNotFindPago extends NotFoundHttpException
{
    public static function withId($id)
    {
        return new self(sprintf('El pago con id [%s] no existe.', $id));
    }
}
