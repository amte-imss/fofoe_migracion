<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrabajadorImss
 */
#[ORM\Entity(repositoryClass: \App\Repository\TrabajadorImssRepository::class)]
#[ORM\Table(name: 'trabajador_imss')]
class TrabajadorImss
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
    #[ORM\Column(type: 'integer', nullable: false)]
    private $matricula;

    /**
     * @var string
     */
    #[ORM\Column(name: 'nombre', type: 'string', length: 100, nullable: true)]
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(name: 'aPaterno', type: 'string', length: 100, nullable: true)]
    private $aPaterno;

    /**
     * @var string
     */
    #[ORM\Column(name: 'aMaterno', type: 'string', length: 100, nullable: true)]
    private $aMaterno;

    /**
     * @var string
     */
    #[ORM\Column(name: 'curp', type: 'string', length: 18, nullable: true)]
    private $curp;

    /**
     * @var string
     */
    #[ORM\Column(name: 'rfc', type: 'string', length: 13, nullable: true)]
    private $rfc;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sexo', type: 'string', length: 10, nullable: true)]
    private $sexo;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fechaIngreso', type: 'date', nullable: true)]
    private $fechaIngreso;

    /**
     * @var Delegacion
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Delegacion::class)]
    #[ORM\JoinColumn(name: 'delegacion_id', referencedColumnName: 'id', nullable: true)]
    private $delegacion;

    /**
     * @var string
     */
    #[ORM\Column(name: 'antiguedad', type: 'string', length: 10, nullable: true)]
    private $antiguedad;

    /**
     * @var Departamento
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Departamento::class)]
    #[ORM\JoinColumn(name: 'departamento_id', referencedColumnName: 'id', nullable: true)]
    private $adscripcion;

    /**
     * @var Categoria
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Categoria::class)]
    #[ORM\JoinColumn(name: 'categoria_id', referencedColumnName: 'id', nullable: true)]
    private $categoria;

    /**
     * @var string
     */
    #[ORM\Column(name: 'correo', type: 'string', length: 255, nullable: true)]
    private $correo;

    /**
     * @var CampoClinico
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\CampoClinico::class, inversedBy: 'trabajadoresBecados')]
    #[ORM\JoinColumn(name: 'campo_clinico_id', referencedColumnName: 'id')]
    private $campoClinico;


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
     * @param integer $matricula
     * @return TrabajadorImss
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return TrabajadorImss
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
     * Set aPaterno
     *
     * @param string $aPaterno
     *
     * @return TrabajadorImss
     */
    public function setAPaterno($aPaterno)
    {
        $this->aPaterno = $aPaterno;

        return $this;
    }

    /**
     * Get aPaterno
     *
     * @return string
     */
    public function getAPaterno()
    {
        return $this->aPaterno;
    }

    /**
     * Set aMaterno
     *
     * @param string $aMaterno
     *
     * @return TrabajadorImss
     */
    public function setAMaterno($aMaterno)
    {
        $this->aMaterno = $aMaterno;

        return $this;
    }

    /**
     * Get aMaterno
     *
     * @return string
     */
    public function getAMaterno()
    {
        return $this->aMaterno;
    }

    /**
     * Set curp
     *
     * @param string $curp
     *
     * @return TrabajadorImss
     */
    public function setCurp($curp)
    {
        $this->curp = $curp;

        return $this;
    }

    /**
     * Get curp
     *
     * @return string
     */
    public function getCurp()
    {
        return $this->curp;
    }

    /**
     * Set rfc
     *
     * @param string $rfc
     *
     * @return TrabajadorImss
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * Get rfc
     *
     * @return string
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * Set sexo
     *
     * @param string $sexo
     *
     * @return TrabajadorImss
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * Get sexo
     *
     * @return string
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * Set fechaIngreso
     *
     * @param \DateTime $fechaIngreso
     *
     * @return TrabajadorImss
     */
    public function setFechaIngreso($fechaIngreso)
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    /**
     * Get fechaIngreso
     *
     * @return \DateTime
     */
    public function getFechaIngreso()
    {
        return $this->fechaIngreso;
    }

    /**
     * Set delegacion
     *
     * @param Delegacion $delegacion
     *
     * @return TrabajadorImss
     */
    public function setDelegacion($delegacion)
    {
        $this->delegacion = $delegacion;

        return $this;
    }

    /**
     * Get delegacion
     *
     * @return Delegacion
     */
    public function getDelegacion()
    {
        return $this->delegacion;
    }

    /**
     * Set antiguedad
     *
     * @param string $antiguedad
     *
     * @return TrabajadorImss
     */
    public function setAngiguedad($antiguedad)
    {
        $this->antiguedad = $antiguedad;

        return $this;
    }

    /**
     * Get angiguedad
     *
     * @return string
     */
    public function getAntiguedad()
    {
        return $this->antiguedad;
    }

    /**
     * Set adscripcion
     *
     * @param Departamento $adscripcion
     *
     * @return TrabajadorImss
     */
    public function setAdscripcion($adscripcion)
    {
        $this->adscripcion = $adscripcion;

        return $this;
    }

    /**
     * Get adscripcion
     *
     * @return Departamento
     */
    public function getAdscripcion()
    {
        return $this->adscripcion;
    }

    /**
     * Set categoria
     *
     * @param Categoria $categoria
     *
     * @return TrabajadorImss
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return Categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set correo
     *
     * @param string $correo
     *
     * @return TrabajadorImss
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;

        return $this;
    }

    /**
     * Get correo
     *
     * @return string
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * @param CampoClinico $campoClinico
     * @return TrabajadorImss
     */
    public function setCampoClinico(?CampoClinico $campoClinico = null)
    {
        $this->campoClinico = $campoClinico;

        return $this;
    }

    /**
     * @return CampoClinico
     */
    public function getCampoClinico()
    {
        return $this->campoClinico;
    }
}

