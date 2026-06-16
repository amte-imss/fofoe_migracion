<?php

namespace App\Service;

use App\Entity\CampoClinico;

interface GeneradorFormatoFofoeInterface
{
    public function responsePdf(string $path, CampoClinico $campoClinico, bool $overwrite = false): string;

    public function getFileName(CampoClinico $campoClinico): string;
}
