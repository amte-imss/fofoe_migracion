<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\RecursoRepository::class)]
#[ORM\Table(name: 'recurso')]
class Recurso
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nombre;

    #[ORM\Column(type: 'integer')]
    private $motivoColaboracionId;

    #[ORM\ManyToOne(targetEntity: \MotivoColaboracion::class, inversedBy: 'recursos')]
    #[ORM\JoinColumn(name: 'motivo_colaboracion_id', referencedColumnName: 'id', nullable: false)]
    private $motivoColaboracion;

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

    public function getMotivoColaboracion()
    {
        return $this->motivoColaboracion;
    }

    public function setMotivoColaboracion(MotivoColaboracion $motivoColaboracion)
    {
        $this->motivoColaboracion = $motivoColaboracion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMotivoColaboracionId()
    {
        return $this->motivoColaboracionId;
    }

    /**
     * @param mixed $motivoColaboracionId
     */
    public function setMotivoColaboracionId($motivoColaboracionId)
    {
        $this->motivoColaboracionId = $motivoColaboracionId;
    }
}