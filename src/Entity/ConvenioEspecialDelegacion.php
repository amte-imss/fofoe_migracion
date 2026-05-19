<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ConvenioEspecialDelegacionRepository::class)]
#[ORM\Table(name: 'convenio_especial_delegacion')]
class ConvenioEspecialDelegacion
{

    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: \ConvenioEspecial::class, inversedBy: 'delegacionConvenios')]
    #[ORM\JoinColumn(name: 'convenio_id', referencedColumnName: 'id')]
    private $convenio;

    #[ORM\ManyToOne(targetEntity: \Delegacion::class, inversedBy: 'delegacionConvenios')]
    #[ORM\JoinColumn(name: 'delegacion_id', referencedColumnName: 'id')]
    private $delegacion;

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
    public function getDelegacion()
    {
        return $this->delegacion;
    }

    /**
     * @param mixed $delegacion
     */
    public function setDelegacion($delegacion)
    {
        $this->delegacion = $delegacion;
    }
}