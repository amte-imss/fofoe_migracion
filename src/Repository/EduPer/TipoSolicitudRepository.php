<?php

namespace App\Repository\EduPer;

use App\Entity\EduPer\TipoSolicitud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<TipoSolicitud> */
class TipoSolicitudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoSolicitud::class);
    }
}
