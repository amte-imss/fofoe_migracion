<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouldNotFoundCedulaIdentificacionFiscal extends NotFoundHttpException
{
    public static function withInstitucionId($id)
    {
        return new self(
            sprintf(
                'La institución [%s] no tiene ninguna cédula de identifición fiscal asociada.',
                $id
            )
        );
    }
}
