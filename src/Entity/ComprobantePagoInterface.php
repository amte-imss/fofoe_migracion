<?php

namespace App\Entity;

interface ComprobantePagoInterface
{
    public function getId();

    public function getReferenciaBancaria();

    public function getMonto();

    public function getFechaPago();
}
