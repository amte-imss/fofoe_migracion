<?php

namespace App\Repository;

use App\Entity\ConveniosNivelAcademico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConveniosNivelAcademicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConveniosNivelAcademico::class);
    }
}
