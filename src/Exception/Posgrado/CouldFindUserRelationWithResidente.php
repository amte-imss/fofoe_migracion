<?php


namespace App\Exception\Posgrado;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouldFindUserRelationWithResidente extends NotFoundHttpException
{
    public static function withId($id)
    {
        return new self(sprintf('El usuario con id [%s] no tiene asociado un residente.', $id));
    }
}
