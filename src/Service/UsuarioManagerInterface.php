<?php

namespace App\Service;

use App\Entity\Usuario;

interface UsuarioManagerInterface
{
    public function update(Usuario $usuario): void;
}
