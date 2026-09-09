<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CampoClinicoEvent extends Event
{
    const CAMPO_REGISTRADO = 'campo.registrado';
}
