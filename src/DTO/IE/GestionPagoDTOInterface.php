<?php

namespace App\DTO\IE;

use App\DTO\IE\GestionPago\CampoClinicoDTO;
use App\DTO\IE\GestionPago\UltimoPagoDTO;
use Doctrine\Common\Collections\ArrayCollection;

interface GestionPagoDTOInterface
{
    public function getNoSolicitud(): string;

    public function getPagos(): ArrayCollection;

    public function getUltimoPago(): UltimoPagoDTO;

    public function getMontoTotal(): float;

    public function getMontoTotalPorPagar(): float;

    public function getTipoPago(): string;

    public function getCampoClinico(): ?CampoClinicoDTO;
}
