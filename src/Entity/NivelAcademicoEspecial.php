<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\NivelAcademicoEspecialRepository::class)]
#[ORM\Table(name: 'nivel_academico_especial')]
class NivelAcademicoEspecial
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nombre;

    #[ORM\OneToMany(targetEntity: \ConvenioEspecial::class, mappedBy: 'NivelAcademicoEspecial')]
    private $convenios;

    public function __construct()
    {
        $this->convenios = new ArrayCollection();
    }

    // Getters y Setters

    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getConvenios()
    {
        return $this->convenios;
    }
}