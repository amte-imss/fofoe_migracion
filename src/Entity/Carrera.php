<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CarreraRepository::class)]
#[ORM\Table(name: 'carrera')]
class Carrera implements \Stringable
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
    private $nombre;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activo;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $is_came;

    /**
     * @var NivelAcademico
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\NivelAcademico::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'nivel_academico_id', referencedColumnName: 'id', nullable: true)]
    private $nivelAcademico;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    private $nivelAcademicoId;

    #[ORM\OneToMany(targetEntity: \App\Entity\MontoCarrera::class, mappedBy: 'carrera')]
    private $montosCarreras;

    #[ORM\OneToMany(targetEntity: \App\Entity\Convenio::class, mappedBy: 'carrera')]
    private $convenios;

    public function __construct()
    {
        $this->montosCarreras = new \Doctrine\Common\Collections\ArrayCollection();
        $this->convenios = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Carrera
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
   * @return string
   */
    public function getDisplayName() {
      return ($this->nivelAcademico ? $this->nivelAcademico->getNombre() : '')
          . " - " . $this->nombre;
    }

    /**
     * @param boolean $activo
     * @return Carrera
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
     * @param boolean $isCame
     * @return boolean
     */
    public function setIsCame($isCame)
    {
        $this->is_came = $isCame;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsCame()
    {
        return $this->is_came;
    }

    /**
     * @param NivelAcademico $nivelAcademico
     * @return Carrera
     */
    public function setNivelAcademico(?NivelAcademico $nivelAcademico = null)
    {
        $this->nivelAcademico = $nivelAcademico;

        return $this;
    }

    /**
     * @return NivelAcademico
     */
    public function getNivelAcademico()
    {
        return $this->nivelAcademico;
    }

    public function __toString(): string
    {
        return $this->getNombre();
    }

    /**
     * @param MontoCarrera $montosCarrera
     * @return Carrera
     */
    public function addMontosCarrera(MontoCarrera $montosCarrera)
    {
        $this->montosCarreras[] = $montosCarrera;

        return $this;
    }

    /**
     * @param MontoCarrera $montosCarrera
     */
    public function removeMontosCarrera(MontoCarrera $montosCarrera)
    {
        $this->montosCarreras->removeElement($montosCarrera);
    }

    /**
     * @return Collection
     */
    public function getMontosCarreras()
    {
        return $this->montosCarreras;
    }

    /**
     * @return mixed
     */
    public function getConvenios()
    {
        return $this->convenios;
    }

    /**
     * @return int
     */
    public function getNivelAcademicoId()
    {
        return $this->nivelAcademicoId;
    }

    /**
     * @param int $nivelAcademicoId
     */
    public function setNivelAcademicoId($nivelAcademicoId)
    {
        $this->nivelAcademicoId = $nivelAcademicoId;
    }

}
