<?php

namespace App\Repository\IE\DetalleSolicitud\Expediente;

final class Documents
{
    public function __construct(
        private readonly OficioMontos  $oficioMontos,
        private readonly array         $comprobantesPago,
        private readonly array         $facturas,
        private readonly ?FormatosFofoe $formatosFofoe = null,
    ) {}

    public function getOficioMontos(): OficioMontos { return $this->oficioMontos; }

    public function getComprobantesPago(): array { return $this->comprobantesPago; }

    public function getFacturas(): array { return $this->facturas; }

    public function getFormatosFofoe(): ?FormatosFofoe { return $this->formatosFofoe; }
}
