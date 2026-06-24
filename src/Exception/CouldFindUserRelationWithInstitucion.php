<?php


namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouldFindUserRelationWithInstitucion extends NotFoundHttpException
{
    public static function withId($id)
    {
        return new self(sprintf('El usuario con id [%s] no tiene asociada una institucion.', $id));
    }
}
