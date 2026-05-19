<?php

namespace App\Repository;

use App\Entity\NivelAcademicoEspecial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NivelAcademicoEspecialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NivelAcademicoEspecial::class);
    }
}
