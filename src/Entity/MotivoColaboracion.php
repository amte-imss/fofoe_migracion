<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\MotivoColaboracionRepository::class)]
#[ORM\Table(name: 'motivo_colaboracion')]
class MotivoColaboracion
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nombre;

    #[ORM\OneToMany(targetEntity: \ConvenioEspecial::class, mappedBy: 'motivoColaboracion')]
    private $convenios;

    #[ORM\OneToMany(targetEntity: \Recurso::class, mappedBy: 'motivoColaboracion')]
    private $recursos;

    public function __construct()
    {
        $this->convenios = new ArrayCollection();
        $this->recursos = new ArrayCollection();
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

    public function getRecursos()
    {
        return $this->recursos;
    }

}