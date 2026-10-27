<?php

namespace App\Repository\Fofoe\ValidacionDePago;

final class CampoClinico
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $sede,
        private readonly ?string $carrera,
        private readonly ?string $horario,
        private readonly ?string $asignatura,
        private readonly mixed $validateFormatoFofoe
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSede(): ?string
    {
        return $this->sede;
    }

    public function getCarrera(): ?string
    {
        return $this->carrera;
    }

    public function getHorario(): ?string
    {
        return $this->horario;
    }

    public function getAsignatura(): ?string
    {
        return $this->asignatura;
    }

    public function getValidateFormatoFofoe(): mixed
    {
        return $this->validateFormatoFofoe;
    }
}
