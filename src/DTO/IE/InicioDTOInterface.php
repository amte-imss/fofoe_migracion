<?php

namespace App\DTO\IE;

interface InicioDTOInterface
{
    public function getId(): int;

    public function getEstatus(): ?string;

    public function getFecha(): ?string;

    public function getNoCamposAutorizados(): int;

    public function getNoCamposSolicitados(): int;

    public function getNoSolicitud(): string;

    public function getTipoPago(): ?string;

    public function getUltimoPago(): ?int;

    public function getDisplayDelegacionUmae(): string;
}
