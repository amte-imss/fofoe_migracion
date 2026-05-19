<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\RoleRepository::class)]
#[ORM\Table(name: 'rol')]
class Rol implements \Stringable
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
    #[ORM\Column(type: 'string', length: 255)]
    private $nombre;

    #[ORM\OneToMany(targetEntity: \App\Entity\Permiso::class, mappedBy: 'rol')]
    private $permisos;

    /**
     * @var String
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $clave;

    public function __construct()
    {
        $this->permisos = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @param Permiso $permiso
     * @return Rol
     */
    public function addPermiso(Permiso $permiso)
    {
        if(!$this->permisos->contains($permiso)) {
            $this->permisos[] = $permiso;
        }

        return $this;
    }

    /**
     * @param Permiso $permiso
     * @return Rol
     */
    public function removePermiso(Permiso $permiso)
    {
        if($this->permisos->contains($permiso)) {
            $this->permisos->removeElement($permiso);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * @return String
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * @param String $clave
     * @return Rol
     */
    public function setClave($clave)
    {
        $this->clave = $clave;
        return $this;
    }

    public function __toString(): string
    {
      return $this->nombre;
    }

}
