<?php

namespace App\DTO\IE;

use App\Entity\Institucion;

class PerfilInstitucionDTO extends Institucion implements PerfilInstitucionDTOInterface
{

    public function getId(): ?int
    {
        return parent::getId();
    }

    public function getNombre(): ?string
    {
        return parent::getNombre();
    }

    public function getRazonSocial(): ?string
    {
        return parent::getRazonSocial();
    }

    public function getRfc(): ?string
    {
        return parent::getRfc();
    }

    public function getDireccion(): ?string
    {
        return parent::getDireccion();
    }

    public function getCorreo(): ?string
    {
        return parent::getCorreo();
    }

    public function getTelefono(): ?string
    {
        return  parent::getTelefono();
    }

    public function getFax(): ?string
    {
        return parent::getFax();
    }

    public function getSitioWeb(): ?string
    {
        return parent::getSitioWeb();
    }

    public function getCedulaIdentificacion(): ?string
    {
        return parent::getCedulaIdentificacion();
    }

    public function getCedulaIdentificacion2(): ?string
    {
        return parent::getCedulaIdentificacion2();
    }

    public function getRepresentante(): ?string
    {
        return parent::getRepresentante();
    }

    public function getConfirmacionInformacion(): ?\DateTimeInterface
    {
        return parent::getConfirmacionInformacion();
    }
}
