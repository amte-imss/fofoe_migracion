<?php

namespace App\Service;

use App\Entity\CampoClinico;

interface CampoClinicoManagerInterface
{
    public function create(CampoClinico $campoClinico): array;

    public function delete(CampoClinico $campoClinico): array;

    public function uploadFormatoFofoe(CampoClinico $campoClinico): array;
}
