<?php

namespace App\DTO\IE\GestionPago;

interface PagoDTOInterface
{
    public function getId(): int;

    public function getReferenciaBancaria(): string;

    public function getFechaPago(): string;

    public function getMonto(): float;

    public function getObservaciones(): ?string;
}
