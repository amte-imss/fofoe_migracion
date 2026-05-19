<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\CoordinacionFirmanteRepository::class)]
#[ORM\Table(name: 'coordinacion_firmante')]
class CoordinacionFirmante implements \Stringable 
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\Length(max: 75)]
    private $nombre;

     /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activa;

     /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $borrado;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $fecha_creacion;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $fecha_modificacion;

    /**
     * @var \stdClass
     */
    private $convenios;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    private $orderSort;

    public function __construct()
    {
        $this->orderSort = 0;
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
     * @return CoordinacionFirmante
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
     * Set activa
     *
     * @param boolean $activa
     *
     * @return CoordinacionFirmante
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
     * Set borrado
     *
     * @param boolean $borrado
     *
     * @return CoordinacionFirmante
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return CoordinacionFirmante
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fecha_creacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fecha_creacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return CoordinacionFirmante
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fecha_modificacion = $fechaModificacion;

        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fecha_modificacion;
    }

    /**
     * Set convenios
     *
     * @param \stdClass $convenios
     *
     * @return CoordinacionFirmante
     */
    public function setConvenios($convenios)
    {
        $this->convenios = $convenios;

        return $this;
    }

    /**
     * Get convenios
     *
     * @return \stdClass
     */
    public function getConvenios()
    {
        return $this->convenios;
    }

    /**
     * @return int
     */
    public function getOrderSort()
    {
        return $this->orderSort;
    }

    /**
     * @param int $sortOrder
     */
    public function setOrderSort($sortOrder)
    {
        $this->orderSort = $sortOrder;
    }

    public function __toString(): string
    {
        return $this->nombre;
    }
}

