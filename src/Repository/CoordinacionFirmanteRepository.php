<?php

namespace App\Repository;

use App\Entity\CoordinacionFirmante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CoordinacionFirmanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoordinacionFirmante::class);
    }
}
