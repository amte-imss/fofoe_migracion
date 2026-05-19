<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ConvenioCicloAcademicoRepository::class)]
#[ORM\Table(name: 'convenio_ciclo_academico')]
class ConvenioCicloAcademico
{

    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: \Convenio::class, inversedBy: 'conveniosCiclosAcademicos')]
    #[ORM\JoinColumn(name: 'convenio_id', referencedColumnName: 'id')]
    private $convenio;

    #[ORM\ManyToOne(targetEntity: \CicloAcademico::class, inversedBy: 'conveniosCiclosAcademicos')]
    #[ORM\JoinColumn(name: 'ciclo_academico_id', referencedColumnName: 'id')]
    private $cicloAcademico;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param mixed $convenio
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
    }

    /**
     * @return mixed
     */
    public function getCicloAcademico()
    {
        return $this->cicloAcademico;
    }

    /**
     * @param mixed $cicloAcademico
     */
    public function setCicloAcademico($cicloAcademico)
    {
        $this->cicloAcademico = $cicloAcademico;
    }
}