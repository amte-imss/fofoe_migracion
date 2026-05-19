<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\DepartmentRepository::class)]
#[ORM\Table(name: 'departamento')]
class Departamento implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Length(max: 100, maxMessage: 'No puede contener más de {{ limit }} carácteres')]
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 15)]
    #[Assert\Length(max: 15, maxMessage: 'No puede contener más de {{ limit }} carácteres')]
    private $claveDepartamental;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 15)]
    #[Assert\Length(max: 15, maxMessage: 'No puede contener más de {{ limit }} carácteres')]
    private $clavePresupuestal;

    /**
     * @var Unidad
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Unidad::class, inversedBy: 'departamentos')]
    #[ORM\JoinColumn(name: 'unidad_id', referencedColumnName: 'id')]
    private $unidad;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $esUnidad;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\Length(max: 4, min: 4, maxMessage: 'No puede contener más de {{ limit }} carácteres', minMessage: 'No puede contener menos de {{ limit }} carácteres')]
    private $anio;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $fecha;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activo;

    #[ORM\OneToMany(targetEntity: \App\Entity\Usuario::class, mappedBy: 'departamento')]
    private $usuarios;

    public function __construct()
    {
        $this->fecha = new \DateTime();
        $this->usuarios = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nombre
     * @return Departamento
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param boolean $activo
     * @return Departamento
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param string $claveDepartamental
     * @return Departamento
     */
    public function setClaveDepartamental($claveDepartamental)
    {
        $this->claveDepartamental = $claveDepartamental;

        return $this;
    }

    /**
     * @return string
     */
    public function getClaveDepartamental()
    {
        return $this->claveDepartamental;
    }

    /**
     * @param string $clavePresupuestal
     * @return Departamento
     */
    public function setClavePresupuestal($clavePresupuestal)
    {
        $this->clavePresupuestal = $clavePresupuestal;

        return $this;
    }

    /**
     * @return string
     */
    public function getClavePresupuestal()
    {
        return $this->clavePresupuestal;
    }

    /**
     * @param boolean $esUnidad
     * @return Departamento
     */
    public function setEsUnidad($esUnidad)
    {
        $this->esUnidad = $esUnidad;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEsUnidad()
    {
        return $this->esUnidad;
    }

    /**
     * @param integer $anio
     * @return Departamento
     */
    public function setAnio($anio)
    {
        $this->anio = $anio;

        return $this;
    }

    /**
     * @return integer
     */
    public function getAnio()
    {
        return $this->anio;
    }

    /**
     * @param \DateTime $fecha
     * @return Departamento
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param Unidad $unidad
     * @return Departamento
     */
    public function setUnidad(?Unidad $unidad = null)
    {
        $this->unidad = $unidad;

        return $this;
    }

    /**
     * @return Unidad
     */
    public function getUnidad()
    {
        return $this->unidad;
    }

    /**
     * @param Usuario $usuario
     * @return Departamento
     */
    public function addUsuario(Usuario $usuario)
    {
        if(!$this->usuarios->contains($usuario)) {
            $this->usuarios[] = $usuario;
        }

        return $this;
    }

    /**
     * @param Usuario $usuario
     */
    public function removeUsuario(Usuario $usuario)
    {
        if($this->usuarios->contains($usuario)) {
            $this->usuarios->removeElement($usuario);
        }
    }

    /**
     * @return Collection
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }
    
    public function __toString(): string {
      return $this->claveDepartamental . " - " .  $this->nombre;
    }
}
