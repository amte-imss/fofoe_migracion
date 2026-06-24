<?php

namespace App\DTO;

class Sied
{
    public ?string $matricula           = null;
    public ?string $delegacion          = null;
    public ?string $nombre              = null;
    public ?string $apaterno            = null;
    public ?string $amaterno            = null;
    public ?string $curp                = null;
    public ?string $rfc                 = null;
    public ?string $sexo                = null;
    public ?string $fechaIngreso        = null;
    public ?string $correoInstitucional = null;
    public ?string $antiguedad          = null;
    public ?int    $adscripcionId       = null;
    public ?string $adscripcion         = null;
    public ?string $claveCategoria      = null;
    public ?string $nombreCategoria     = null;

    // Dato no proveniente de SIED, se calcula con adscripcion
    public string $unidad = '';
}
