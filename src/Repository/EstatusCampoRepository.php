<?php

namespace App\Repository;

use App\Entity\EstatusCampo;
use App\Entity\EstatusCampoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstatusCampoRepository extends ServiceEntityRepository implements EstatusCampoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstatusCampo::class);
    }

    public function getEstatusPagado(): mixed
    {
        return $this->getEstatusByNombre(EstatusCampoInterface::PAGO);
    }

    private function getEstatusByNombre($nombre)
    {
        return $this->findOneBy(['nombre' =>  $nombre]);
    }
}
