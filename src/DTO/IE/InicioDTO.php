<?php

namespace App\DTO\IE;

use App\DTO\Entity\Solicitud;
use App\Entity\Solicitud as SolicitudBase;
use App\Entity\SolicitudInterface;
use App\Entity\SolicitudTipoPagoInterface;
use App\Repository\PagoRepository;

final class InicioDTO extends Solicitud implements InicioDTOInterface
{
    public function __construct(SolicitudBase $solicitud)
    {
        parent::__construct($solicitud);
    }

    public function getId(): int
    {
        return parent::getId();
    }

    public function getNoCamposSolicitados(): int
    {
        return parent::getNoCamposSolicitados();
    }

    public function getNoCamposAutorizados(): int
    {
        return parent::getNoCamposAutorizados();
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return parent::getFecha();
    }

    public function getTipoPago(): ?string
    {
        return parent::getTipoPago();
    }

    public function getNoSolicitud() : string
    {
        return parent::getNoSolicitud();
    }

    public function getEstatus(): ?string
    {
        return parent::getEstatus();
    }

    public function getUltimoPago(): ?int
    {
        if (
            $this->estatus === SolicitudInterface::CARGANDO_COMPROBANTES &&
            $this->tipoPago === SolicitudTipoPagoInterface::TIPO_PAGO_UNICO
        ) {
            $criteria = PagoRepository::getPagosByReferenciaBancaria($this->referenciaBancaria);

            return $this->getPagos()
                ->matching($criteria)
                ->first()
                ->getId();
        }

        return null;
    }

    public function getDisplayDelegacionUmae(): string
    {
        return $this->getDelegacion()->getNombre()
            . ($this->getEsUMAE() ? ' / ' . $this->getUnidad()->getNombre() : '');
    }
}
