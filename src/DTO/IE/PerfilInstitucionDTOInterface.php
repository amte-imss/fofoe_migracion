<?php

namespace App\DTO\IE;

interface PerfilInstitucionDTOInterface
{
    public function getId(): ?int;

    public function getNombre(): ?string;

    public function getRazonSocial(): ?string;

    public function getRfc(): ?string;

    public function getDireccion(): ?string;

    public function getCorreo(): ?string;

    public function getTelefono(): ?string;

    public function getFax(): ?string;

    public function getSitioWeb(): ?string;

    public function getCedulaIdentificacion(): ?string;

    public function getCedulaIdentificacion2(): ?string;

    public function getRepresentante(): ?string;

    public function getConfirmacionInformacion(): ?\DateTimeInterface;
}
