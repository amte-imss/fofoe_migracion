<?php

namespace App\Repository;

use App\Entity\ConvenioEspecialDelegacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConvenioEspecialDelegacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConvenioEspecialDelegacion::class);
    }
}
