<?php

namespace App\Repository;

use App\Entity\Pago;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pago::class);
    }

    public function getTotalPagosByYear(string $anio): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.fechaCreacion >= :date1')
            ->andWhere('p.fechaCreacion <= :date2')
            ->setParameter('date1', $anio . '-01-01')
            ->setParameter('date2', $anio . '-12-31')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPagoPendienteByEscuelaEnfermeria(int $escuelaEnfermeriaId): Pago
    {
        return $this->createQueryBuilder('pago')
            ->where('pago.escuelaEnfermeriaSolicitudId = :id')
            ->andWhere('pago.fechaPago IS NULL')
            ->setParameter('id', $escuelaEnfermeriaId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Devuelve los pagos de Escuelas de Enfermería filtrados por estado.
     *
     * Estados:
     *   wait_validation  → validado IS NULL
     *   validated        → validado = true (y si requiere factura, ya fue generada)
     *   rejected         → validado = false
     *   invoice_pending  → validado = true, requiereFactura = true, facturaGenerada IS FALSE/NULL
     */
    public function findEscuelaEnfermeriaByStatus(string $status): array
    {
        $qb = $this->createQueryBuilder('pago')
            ->where('pago.escuelaEnfermeriaSolicitudId IS NOT NULL');

        if ($status === 'wait_validation') {
            $qb->andWhere('pago.validado IS NULL');
        } elseif ($status === 'validated') {
            $qb->andWhere(
                '(pago.validado = true AND pago.requiereFactura = false)
                 OR (pago.validado = true AND pago.requiereFactura = true AND pago.facturaGenerada = true)'
            );
        } elseif ($status === 'rejected') {
            $qb->andWhere('pago.validado = false');
        } elseif ($status === 'invoice_pending') {
            $qb->andWhere(
                'pago.validado = true AND pago.requiereFactura = true
                 AND (pago.facturaGenerada = false OR pago.facturaGenerada IS NULL)'
            );
        } else {
            $qb->andWhere('pago.id < 0');
        }

        return $qb->getQuery()->getResult();
    }
}
