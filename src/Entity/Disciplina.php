<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Disciplina
 */
#[ORM\Entity(repositoryClass: \App\Repository\DisciplinaRepository::class)]
#[ORM\Table(name: 'disciplina')]
class Disciplina implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'nombre', type: 'string', length: 255)]
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(name: 'descripcion', type: 'text', nullable: true)]
    private $descripcion;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'borrado', type: 'boolean')]
    private $borrado;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'activa', type: 'boolean')]
    private $activa;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fecha_creacion', type: 'datetime')]
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fecha_modificacion', type: 'datetime')]
    private $fechaModificacion;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    private $orderSort;

    public function __construct()
    {
        $this->fechaCreacion = Carbon::now();
        $this->fechaModificacion = Carbon::now();
        $this->activa = true;
        $this->borrado = false;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Disciplina
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Disciplina
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set borrado
     *
     * @param boolean $borrado
     *
     * @return Disciplina
     */
    public function setBorrado($borrado)
    {
        $this->borrado = $borrado;

        return $this;
    }

    /**
     * Get borrado
     *
     * @return bool
     */
    public function getBorrado()
    {
        return $this->borrado;
    }

    /**
     * Set activa
     *
     * @param boolean $activa
     *
     * @return Disciplina
     */
    public function setActiva($activa)
    {
        $this->activa = $activa;

        return $this;
    }

    /**
     * Get activa
     *
     * @return bool
     */
    public function getActiva()
    {
        return $this->activa;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return Disciplina
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return Disciplina
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;

        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * @return int
     */
    public function getOrderSort()
    {
        return $this->orderSort;
    }

    /**
     * @param int $orderSort
     */
    public function setOrderSort($orderSort)
    {
        $this->orderSort = $orderSort;
    }

    public function __toString(): string
    {
        return $this->nombre;
    }
}

