<?php

namespace App\DTO\IE;

use App\DTO\IE\GestionPago\CampoClinicoDTO;
use App\DTO\IE\GestionPago\PagoDTO;
use App\DTO\IE\GestionPago\UltimoPagoDTO;
use App\Entity\CampoClinico;
use App\Entity\Pago;
use App\Entity\SolicitudTipoPagoInterface;
use App\Repository\PagoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GestionPagoDTO implements GestionPagoDTOInterface
{
    private readonly mixed $solicitud;
    private readonly ?CampoClinico $campoClinico;
    private readonly Pago $pago;

    private function __construct(Pago $pago)
    {
        $this->pago         = $pago;
        $this->solicitud    = $pago->getSolicitud();
        $this->campoClinico = $this->solicitud->getCampoClinicoByReferenciaBancaria($this->pago->getReferenciaBancaria());
    }

    public static function create(Pago $pago): self
    {
        return new self($pago);
    }

    public function getPagos(): ArrayCollection
    {
        $pagos = new ArrayCollection();

        foreach ($this->solicitud->getPagosByReferenciaBancaria($this->pago->getReferenciaBancaria()) as $pago) {
            $pagos->add(new PagoDTO($pago));
        }

        return $pagos;
    }

    public function getUltimoPago(): UltimoPagoDTO
    {
        $referenciaBancaria = $this->solicitud->isPagoUnico()
            ? $this->solicitud->getReferenciaBancaria()
            : $this->campoClinico->getReferenciaBancaria();

        /** @var Collection $result */
        $result = $this->solicitud->getPagos()->matching(
            PagoRepository::getUltimoPagoByCriteria($referenciaBancaria)
        );

        return new UltimoPagoDTO($result->first());
    }

    public function getMontoTotal(): float
    {
        return $this->solicitud->isPagoUnico()
            ? $this->solicitud->getMonto()
            : $this->campoClinico->getMonto();
    }

    public function getIdInstitucion(): int
    {
        return $this->solicitud->getInstitucion()->getId();
    }

    public function getMontoTotalPorPagar(): float
    {
        $pagos = $this->solicitud->isPagoUnico()
            ? $this->solicitud->getPagos()
            : $this->campoClinico->getPagos();

        $amountCarry = array_reduce(
            $pagos->toArray(),
            function (float $carry, Pago $pago): float {
                if (!$pago->getComprobantePago()) return $carry;
                return $carry + (float) $pago->getMonto();
            },
            0.0
        );

        if (!$amountCarry) return $this->getMonto();

        return $this->getMonto() - $amountCarry;
    }

    public function getNoSolicitud(): string
    {
        return $this->solicitud->getNoSolicitud();
    }

    public function getTipoPago(): string
    {
        return $this->solicitud->getTipoPago();
    }

    public function getCampoClinico(): ?CampoClinicoDTO
    {
        if ($this->solicitud->getTipoPago() === SolicitudTipoPagoInterface::TIPO_PAGO_UNICO) {
            return null;
        }

        return new CampoClinicoDTO($this->campoClinico);
    }

    public function getReferenciaBancaria(): string
    {
        return $this->solicitud->isPagoUnico()
            ? $this->solicitud->getReferenciaBancaria()
            : $this->campoClinico->getReferenciaBancaria();
    }

    protected function getMonto(): float
    {
        return $this->solicitud->isPagoUnico()
            ? $this->solicitud->getMonto()
            : $this->campoClinico->getMonto();
    }
}
