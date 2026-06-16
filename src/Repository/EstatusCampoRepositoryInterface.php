<?php

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;

interface EstatusCampoRepositoryInterface extends ObjectRepository
{
    public function getEstatusPagado(): mixed;
}
