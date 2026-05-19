<?php

namespace App\Service;

use App\Entity\Pago;
use Doctrine\ORM\EntityManagerInterface;

class GeneradorRefenciaBancaria2025
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function generateNextReference(int $index = 0): string
    {
        $anioActual = date('Y');
        $year       = date('y');

        $total = (int) $this->entityManager
            ->getRepository(Pago::class)
            ->getTotalPagosByYear($anioActual);

        $total++;
        $total += $index;

        return $year . str_pad((string) $total, 5, '0', STR_PAD_LEFT);
    }
}
