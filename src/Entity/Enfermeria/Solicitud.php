<?php

namespace App\Entity\Enfermeria;

use App\Entity\Unidad;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\Enfermeria\SolicitudRepository::class)]
#[ORM\Table(name: 'lote_escuelas_enfermeria')]
class Solicitud
{

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private $periodo;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', nullable: false)]
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', nullable: false)]
    private $fechaFin;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Unidad::class, inversedBy: 'enfermeriaSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'escuela_id', referencedColumnName: 'id')]
    private $unidad;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\OneToMany(targetEntity: \App\Entity\Enfermeria\Alumno::class, mappedBy: 'solicitud')]
    private $alumnos;

    #[ORM\Column(type: 'boolean', options: ['default' => false], nullable: true)]
    private $isTest;

    public function __construct()
    {
        $this->fechaInicio = Carbon::now();
        $this->fechaFin = Carbon::now();
        $this->createdAt = Carbon::now();
        $this->isTest = false;
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
     * @return string
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * @param string $periodo
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;
    }

    /**
     * @return Carbon|DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * @param Carbon|DateTime $fechaInicio
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    }

    /**
     * @return Carbon|DateTime
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * @param Carbon|DateTime $fechaFin
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    }

    /**
     * @return mixed
     */
    public function getUnidad()
    {
        return $this->unidad;
    }

    /**
     * @param mixed $unidad
     */
    public function setUnidad($unidad)
    {
        $this->unidad = $unidad;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAtFormatted()
    {
        return $this->createdAt->format('d-m-Y H:i:s');
    }

    public function getFechaInicioFormatted()
    {
        return $this->fechaInicio->format('d-m-Y');
    }

    public function getFechaFinFormatted()
    {
        return $this->fechaFin->format('d-m-Y');
    }

    public function getPeriodoFormatted()
    {
        $values = [
            'cuatri' => 'Cuatrimestre',
            'semestre' => 'Semestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual'
        ];
        return $values[$this->getPeriodo()] ?? 'Sin asignar';
    }

    /**
     * @return mixed
     */
    public function getAlumnos()
    {
        return $this->alumnos;
    }

    /**
     * @param mixed $alumnos
     */
    public function setAlumnos($alumnos)
    {
        $this->alumnos = $alumnos;
    }

    /**
     * @return mixed
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * @param mixed $isTest
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
    }
}