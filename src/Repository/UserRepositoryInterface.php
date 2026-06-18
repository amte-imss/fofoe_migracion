<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Usuario>
 */
interface UserRepositoryInterface extends ObjectRepository
{
    public function findUserCAMEByDelegacion(int $idDel): array;

    public function findUserJDESByUnidad(int $idUnidad): array;
}
