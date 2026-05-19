<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConveniosDisciplina
 */
#[ORM\Entity(repositoryClass: \App\Repository\ConveniosDisciplinaRepository::class)]
#[ORM\Table(name: 'convenios_disciplina')]
class ConveniosDisciplina
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date_modified', type: 'date')]
    private $dateModified;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'borrado', type: 'boolean')]
    private $borrado;

    /**
     * @var string
     */
    #[ORM\Column(name: 'descripcion', type: 'string', length: 255)]
    private $descripcion;
    
    /**
     * @var Convenio
     */
    #[ORM\Column(name: 'convenio_id', type: 'integer')]
    #[ORM\OneToOne(targetEntity: \App\Entity\Convenio::class, inversedBy: 'conveniosDisciplina')]
    #[ORM\JoinColumn(name: 'convenio_disciplina_id', referencedColumnName: 'id')]
    private $convenioId;

    /**
     * @var int
     */
    #[ORM\Column(name: 'disciplina_id', type: 'integer')]
    private $disciplinaId;


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
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return ConveniosDisciplina
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set borrado
     *
     * @param boolean $borrado
     *
     * @return ConveniosDisciplina
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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return ConveniosDisciplina
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
     * Set convenioId
     *
     * @param integer $convenioId
     *
     * @return ConveniosDisciplina
     */
    public function setConvenioId($convenioId)
    {
        $this->convenioId = $convenioId;

        return $this;
    }

    /**
     * Get convenioId
     *
     * @return int
     */
    public function getConvenioId()
    {
        return $this->convenioId;
    }

    /**
     * Set disciplinaId
     *
     * @param integer $disciplinaId
     *
     * @return ConveniosDisciplina
     */
    public function setDisciplinaId($disciplinaId)
    {
        $this->disciplinaId = $disciplinaId;

        return $this;
    }

    /**
     * Get disciplinaId
     *
     * @return int
     */
    public function getDisciplinaId()
    {
        return $this->disciplinaId;
    }
}

