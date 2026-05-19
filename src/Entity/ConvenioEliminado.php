<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * ConvenioEliminado
 */
#[ORM\Entity(repositoryClass: \App\Repository\ConvenioEliminadoRepository::class)]
#[ORM\Table(name: 'convenios_eliminados')]
class ConvenioEliminado
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'convenio_info', type: 'text', nullable: true)]
    private $convenioInfo;

    /**
     * @var \App\Entity\Usuario|null
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: true)]
    private $usuario;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fecha_eliminacion', type: 'datetime')]
    private $fechaEliminacion;

    public function __construct()
    {
        $this->fechaEliminacion = new \DateTime(); // Valor por defecto
    }

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'rfc', type: 'string', length: 25, nullable: true)]
    private $rfc;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'fuica', type: 'string', length: 50, nullable: true)]
    private $fuica;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'numero', type: 'integer', nullable: true)]
    private $numero;

    // Getters y Setters

    public function getId()
    {
        return $this->id;
    }

    public function getConvenioInfo()
    {
        return $this->convenioInfo;
    }

    public function setConvenioInfo($convenioInfo)
    {
        $this->convenioInfo = $convenioInfo;
        return $this;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario(?\App\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getFechaEliminacion()
    {
        return $this->fechaEliminacion;
    }

    public function setFechaEliminacion(\DateTime $fechaEliminacion)
    {
        $this->fechaEliminacion = $fechaEliminacion;
        return $this;
    }


    public function getRfc()
    {
        return $this->rfc;
    }

    public function setRfc($rfc)
    {
        $this->rfc = $rfc;
        return $this;
    }

    public function getFuica()
    {
        return $this->fuica;
    }

    public function setFuica($fuica)
    {
        $this->fuica = $fuica;
        return $this;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function setNumero($numero)
    {
        $this->numero = $numero;
        return $this;
    }
}