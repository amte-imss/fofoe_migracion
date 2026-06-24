<?php

namespace App\Calculator\Posgrado;

use App\Entity\Posgrado\Residencia;
use App\Repository\ConfiguracionGlobalRepositoryInterface;

class ResidenteExtranjeroNoImssCalculator implements ResidenteExtranjeroNoImssCalculatorInterface
{
    const CLAVE_CONFIG_MONTO_RES_NO_IMSS  = 'POSGR_RES_EX_NO_IMMS_MONTO';
    const CLAVE_CONFIG_MONEDA_RES_NO_IMSS = 'POSGR_RES_EX_NO_IMMS_MONEDA';

    public function __construct(
        private readonly ConfiguracionGlobalRepositoryInterface $configuracionGlobalRepository
    ) {}

    public function getMontoAPagar(Residencia $residencia, bool $setVal = false): float
    {
        $numMeses = 1 + $residencia->getMonths();

        $configMonto  = $this->configuracionGlobalRepository->findOneBy(['clave' => self::CLAVE_CONFIG_MONTO_RES_NO_IMSS]);
        $montoResidencia = $configMonto ? floatval($configMonto->getValor()) : 0.0;

        $configMoneda = $this->configuracionGlobalRepository->findOneBy(['clave' => self::CLAVE_CONFIG_MONEDA_RES_NO_IMSS]);
        $tipoMoneda   = $configMoneda ? $configMoneda->getValor() : 'USD';

        if ($setVal) {
            $residencia->setMonto($montoResidencia);
            $residencia->setTipoMoneda($tipoMoneda);
        }

        $tasaCambio = $residencia->getTasaCambio() ?: 1.0;

        return $numMeses * $montoResidencia * $tasaCambio;
    }
}
