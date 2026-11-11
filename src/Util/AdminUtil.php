<?php

namespace App\Util;

use App\Entity\Permiso;

final class AdminUtil
{
    private function __construct() {}

    public static function getPermissionName(Permiso $permiso): string
    {
        return sprintf('%s (%s)', $permiso->getClave(), $permiso->getNombre());
    }
}
