<?php

namespace App\Entity\Simulacion;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'simulacion_solicitud_old')]
class SolicitudOld
{

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;


    #[ORM\ManyToOne(targetEntity: \App\Entity\Institucion::class, inversedBy: 'simulacionSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id')]
    private $institucion;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Simulacion\Curso::class, inversedBy: 'simulacionSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'curso_id', referencedColumnName: 'id')]
    private $curso;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private $escolaridad;


    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $numHoras;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $minParticipantes;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $maxParticipantes;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: false)]
    private $monto;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * @param mixed $institucion
     */
    public function setInstitucion($institucion)
    {
        $this->institucion = $institucion;
    }

    /**
     * @return string
     */
    public function getEscolaridad()
    {
        return $this->escolaridad;
    }

    /**
     * @param string $escolaridad
     */
    public function setEscolaridad($escolaridad)
    {
        $this->escolaridad = $escolaridad;
    }

    /**
     * @return int
     */
    public function getNumHoras()
    {
        return $this->numHoras;
    }

    /**
     * @param int $numHoras
     */
    public function setNumHoras($numHoras)
    {
        $this->numHoras = $numHoras;
    }

    /**
     * @return int
     */
    public function getMinParticipantes()
    {
        return $this->minParticipantes;
    }

    /**
     * @param int $minParticipantes
     */
    public function setMinParticipantes($minParticipantes)
    {
        $this->minParticipantes = $minParticipantes;
    }

    /**
     * @return int
     */
    public function getMaxParticipantes()
    {
        return $this->maxParticipantes;
    }

    /**
     * @param int $maxParticipantes
     */
    public function setMaxParticipantes($maxParticipantes)
    {
        $this->maxParticipantes = $maxParticipantes;
    }

    /**
     * @return float
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * @param float $monto
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;
    }

    /**
     * @return mixed
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * @param mixed $curso
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    }
}