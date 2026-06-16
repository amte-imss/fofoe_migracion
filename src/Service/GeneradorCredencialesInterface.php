<?php

namespace App\Service;

use App\Entity\CampoClinico;
use Symfony\Component\HttpFoundation\Response;

interface GeneradorCredencialesInterface
{
    public function responsePdf(string $path, CampoClinico $campoClinico, bool $overwrite = false): Response;
}
