<?php

namespace App\DTO\IE;

interface InicioDTOInterface
{
    public function getId(): int;

    public function getEstatus(): ?string;

    public function getFecha(): ?\DateTimeInterface;

    public function getNoCamposAutorizados(): int;

    public function getNoCamposSolicitados(): int;

    public function getNoSolicitud(): string;

    public function getTipoPago(): ?string;

    public function getUltimoPago(): ?int;

    public function getDisplayDelegacionUmae(): string;
}
