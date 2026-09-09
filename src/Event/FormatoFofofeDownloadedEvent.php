<?php

namespace App\Event;

use App\Entity\CampoClinico;
use Symfony\Contracts\EventDispatcher\Event;

class FormatoFofofeDownloadedEvent extends Event
{
    const NAME = 'formato_fofoe.downloaded';

    public function __construct(
        private readonly CampoClinico $campoClinico,
    ) {}

    public function getCampoClinico(): CampoClinico
    {
        return $this->campoClinico;
    }
}
