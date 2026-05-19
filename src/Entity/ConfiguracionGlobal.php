<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfiguracionGlobal
 */
#[ORM\Entity(repositoryClass: \App\Repository\ConfiguracionGlobalRepository::class)]
#[ORM\Table(name: 'configuracion_global')]
class ConfiguracionGlobal
{

    const POSGRADO_CAME = 'POSGRADO_CAME';


    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'clave', type: 'string', length: 50, unique: true)]
    private $clave;

    /**
     * @var string
     */
    #[ORM\Column(name: 'valor', type: 'string', length: 255)]
    private $valor;

    /**
     * @var string
     */
    #[ORM\Column(name: 'descripcion', type: 'text', nullable: true)]
    private $descripcion;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'activo', type: 'boolean', nullable: true)]
    private $activo;


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
     * Set clave
     *
     * @param string $clave
     *
     * @return ConfiguracionGlobal
     */
    public function setClave($clave)
    {
        $this->clave = $clave;

        return $this;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return ConfiguracionGlobal
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return ConfiguracionGlobal
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
     * Set activo
     *
     * @param boolean $activo
     *
     * @return ConfiguracionGlobal
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return bool
     */
    public function getActivo()
    {
        return $this->activo;
    }
}

