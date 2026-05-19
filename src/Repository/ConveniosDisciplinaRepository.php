<?php

namespace App\Repository;

use App\Entity\ConveniosDisciplina;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConveniosDisciplinaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConveniosDisciplina::class);
    }
}
