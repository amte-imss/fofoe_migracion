<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CicloAcademicoRepository::class)]
#[ORM\Table(name: 'ciclo_academico')]
class CicloAcademico implements \Stringable
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
    #[ORM\Column(type: 'string', length: 30)]
    private $nombre;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activo;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    private $orderSort;

    #[ORM\OneToMany(targetEntity: \ConvenioCicloAcademico::class, mappedBy: 'convenio')]
    private $conveniosCiclosAcademicos;


    public function __construct()
    {
        $this->orderSort = 0;
        $this->conveniosCiclosAcademicos = new ArrayCollection();
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
     * @return CicloAcademico
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
     * @return CicloAcademico
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

    public function __toString(): string
    {
        return $this->getNombre();
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

    public function getName()
    {
        return $this->getNombre();
    }

    /**
     * @return ArrayCollection
     */
    public function getConveniosCiclosAcademicos()
    {
        return $this->conveniosCiclosAcademicos;
    }

    /**
     * @param ArrayCollection $conveniosCiclosAcademicos
     */
    public function setConveniosCiclosAcademicos($conveniosCiclosAcademicos)
    {
        $this->conveniosCiclosAcademicos = $conveniosCiclosAcademicos;
    }

    public function addConvenioCicloAcademico(ConvenioCicloAcademico $convenioCicloAcademico)
    {
        $this->conveniosCiclosAcademicos[] = $convenioCicloAcademico;
        return $this;
    }

    public function removeConvenioCicloAcademico(ConvenioCicloAcademico $convenioCicloAcademico)
    {
        $this->conveniosCiclosAcademicos->removeElement($convenioCicloAcademico);
    }
}
