<?php

namespace App\Entity\Posgrado;

use App\Entity\Pago;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: \App\Repository\Posgrado\ResidenciaRepository::class)]
#[ORM\Table(name: 'posgrado_residencia')]
class Residencia implements ResidenciaInterface
{
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
    #[ORM\Column(name: 'categoria', type: 'string', length: 50, nullable: true)]
    private $categoria;

    /**
     * @var string
     */
    #[ORM\Column(name: 'delegacion', type: 'string', length: 100, nullable: true)]
    private $delegacion;

    /**
     * @var string
     */
    #[ORM\Column(name: 'tipo_especialidad', type: 'string', length: 15, nullable: true)]
    private $tipoEspecialidad;

    /**
     * @var string
     */
    #[ORM\Column(name: 'especialidad', type: 'string', length: 100, nullable: true)]
    private $especialidad;

    /**
     * @var string
     */
    #[ORM\Column(name: 'clues', type: 'string', length: 20, nullable: true)]
    private $clues;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sede', type: 'string', length: 150, nullable: true)]
    private $sede;

    /**
     * @var string
     */
    #[ORM\Column(name: 'subsede', type: 'string', length: 255, nullable: true)]
    private $subsede;

    /**
     * @var int
     */
    #[ORM\Column(name: 'nivel_atencion', type: 'integer', nullable: true)]
    private $nivelAtencion;

    /**
     * @var string
     */
    #[ORM\Column(name: 'tipo_delegacion_umae', type: 'string', length: 20, nullable: true)]
    private $tipoDelegacionUmae;

    /**
     * @var string
     */
    #[ORM\Column(name: 'grado', type: 'string', length: 20, nullable: true)]
    private $grado;

    /**
     * @var string
     */
    #[ORM\Column(name: 'folio', type: 'string', length: 30, nullable: true)]
    private $folio;

    /**
     * @var string
     */
    #[ORM\Column(name: 'tipo', type: 'string', length: 20, nullable: true)]
    private $tipo;

    /**
     * @var string
     */
    #[ORM\Column(name: 'ciclo', type: 'string', length: 20, nullable: true)]
    private $ciclo;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Posgrado\Residente::class, inversedBy: 'residencias', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'residente_id', referencedColumnName: 'id')]
    private $residente;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
    private $monto;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private $tipoMoneda;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
    private $tasaCambio;

    #[ORM\OneToMany(targetEntity: \App\Entity\Pago::class, mappedBy: 'residencia', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'residente_id', referencedColumnName: 'id')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $pagos;

    /**
     * @var string
     */
    #[ORM\Column(name: 'estatus', type: 'string', length: 100, nullable: true)]
    private $estatus;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fecha_inicio', type: 'date', nullable: true)]
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'fecha_termino', type: 'date', nullable: true)]
    private $fechaTermino;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $oficioAceptacion;

    #[Vich\UploadableField(mapping: 'residente_cedulas', fileNameProperty: 'oficioAceptacion')]
    #[Assert\File(
        maxSize: '2m',
        mimeTypes: ['application/pdf', 'application/x-pdf'],
        mimeTypesMessage: 'Solo se admiten archivos PDF'
    )]
    #[Assert\File(maxSize: '2m', mimeTypes: ['application/pdf', 'application/x-pdf'], mimeTypesMessage: 'Solo se admiten archivos PDF')]
    protected $oficioFile;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $fechaOficioAceptacion;

    /**
     * @var CargaMasiva
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Posgrado\CargaMasiva::class, inversedBy: 'residencias')]
    #[ORM\JoinColumn(name: 'carga_masiva_id', referencedColumnName: 'id')]
    private $cargaMasiva;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $deleted_at;

    #[ORM\Column(type: 'boolean', options: ['default' => false], nullable: true)]
    private $isTest;


    public function __construct()
    {
        $this->pagos = new ArrayCollection();
        $this->isTest = false;
    }


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
     * Set categoria
     *
     * @param string $categoria
     *
     * @return Residencia
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return string
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set delegacion
     *
     * @param string $delegacion
     *
     * @return Residencia
     */
    public function setDelegacion($delegacion)
    {
        $this->delegacion = $delegacion;

        return $this;
    }

    /**
     * Get delegacion
     *
     * @return string
     */
    public function getDelegacion()
    {
        return $this->delegacion;
    }

    /**
     * Set tipoEspecialidad
     *
     * @param string $tipoEspecialidad
     *
     * @return Residencia
     */
    public function setTipoEspecialidad($tipoEspecialidad)
    {
        $this->tipoEspecialidad = $tipoEspecialidad;

        return $this;
    }

    /**
     * Get tipoEspecialidad
     *
     * @return string
     */
    public function getTipoEspecialidad()
    {
        return $this->tipoEspecialidad;
    }

    /**
     * Set especialidad
     *
     * @param string $especialidad
     *
     * @return Residencia
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set clues
     *
     * @param string $clues
     *
     * @return Residencia
     */
    public function setClues($clues)
    {
        $this->clues = $clues;

        return $this;
    }

    /**
     * Get clues
     *
     * @return array
     */
    public function getClues()
    {
        return $this->clues;
    }

    /**
     * Set sede
     *
     * @param string $sede
     *
     * @return Residencia
     */
    public function setSede($sede)
    {
        $this->sede = $sede;

        return $this;
    }

    /**
     * Get sede
     *
     * @return string
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set subsede
     *
     * @param string $subsede
     *
     * @return Residencia
     */
    public function setSubsede($subsede)
    {
        $this->subsede = $subsede;

        return $this;
    }

    /**
     * Get subsede
     *
     * @return string
     */
    public function getSubsede()
    {
        return $this->subsede;
    }

    /**
     * Set nivelAtencion
     *
     * @param integer $nivelAtencion
     *
     * @return Residencia
     */
    public function setNivelAtencion($nivelAtencion)
    {
        $this->nivelAtencion = $nivelAtencion;

        return $this;
    }

    /**
     * Get nivelAtencion
     *
     * @return int
     */
    public function getNivelAtencion()
    {
        return $this->nivelAtencion;
    }

    /**
     * Set tipoDelegacionUmae
     *
     * @param string $tipoDelegacionUmae
     *
     * @return Residencia
     */
    public function setTipoDelegacionUmae($tipoDelegacionUmae)
    {
        $this->tipoDelegacionUmae = $tipoDelegacionUmae;

        return $this;
    }

    /**
     * Get tipoDelegacionUmae
     *
     * @return string
     */
    public function getTipoDelegacionUmae()
    {
        return $this->tipoDelegacionUmae;
    }

    /**
     * Set grado
     *
     * @param string $grado
     *
     * @return Residencia
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;

        return $this;
    }

    /**
     * Get grado
     *
     * @return string
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set folio
     *
     * @param string $folio
     *
     * @return Residencia
     */
    public function setFolio($folio)
    {
        $this->folio = $folio;

        return $this;
    }

    /**
     * Get folio
     *
     * @return string
     */
    public function getFolio()
    {
        return $this->folio;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Residencia
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set ciclo
     *
     * @param string $ciclo
     *
     * @return Residencia
     */
    public function setCiclo($ciclo)
    {
        $this->ciclo = $ciclo;

        return $this;
    }

    /**
     * Get ciclo
     *
     * @return string
     */
    public function getCiclo()
    {
        return $this->ciclo;
    }

    /**
     * Set residente
     *
     * @param Residente $residente
     *
     * @return Residencia
     */
    public function setResidente(Residente $residente)
    {
        $this->residente = $residente;

        return $this;
    }

    /**
     * Get residente
     *
     * @return Residente
     */
    public function getResidente()
    {
        return $this->residente;
    }

    /**
     * @param float $monto
     * @return Residencia
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * @return float
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * @param string $tipoMoneda
     * @return Residencia
     */
    public function setTipoMoneda($tipoMoneda)
    {
        $this->tipoMoneda = $tipoMoneda;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoMoneda()
    {
        return $this->tipoMoneda;
    }

    /**
     * Set tasaCambio
     *
     * @param float $tasaCambio
     *
     * @return Residencia
     */
    public function setTasaCambio($tasaCambio)
    {
        $this->tasaCambio = $tasaCambio;

        return $this;
    }

    /**
     * Get tasaCambio
     *
     * @return float
     */
    public function getTasaCambio()
    {
        return $this->tasaCambio;
    }

    /**
     * @param Pago $pago
     * @return Residencia
     */
    public function addPago(Pago $pago)
    {
        $this->pagos[] = $pago;

        return $this;
    }

    /**
     * @param Pago $pago
     */
    public function removePago(Pago $pago)
    {
        $this->pagos->removeElement($pago);
    }

    public function getLastPago()
    {
        return $this->pagos->count() > 0 ? $this->pagos->last() : null;
    }

    /**
     * @return Collection
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * Set estatus
     *
     * @param string $estatus
     *
     * @return Residencia
     */
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;

        return $this;
    }

    /**
     * Get estatus
     *
     * @return string
     */
    public function getEstatus()
    {
        return $this->estatus;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return Residencia
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaTermino
     *
     * @param \DateTime $fechaTermino
     *
     * @return Residencia
     */
    public function setFechaTermino($fechaTermino)
    {
        $this->fechaTermino = $fechaTermino;

        return $this;
    }

    /**
     * Get fechaTermino
     *
     * @return \DateTime
     */
    public function getFechaTermino()
    {
        return $this->fechaTermino;
    }

    /**
     * @param string $oficioAceptacion
     * @return Residencia
     */
    public function setOficioAceptacion($oficioAceptacion)
    {
        $this->oficioAceptacion = $oficioAceptacion;

        return $this;
    }

    /**
     * @return string
     */
    public function getOficioAceptacion()
    {
        return $this->oficioAceptacion;
    }

    /**
     * @return File
     */
    public function getOficioFile()
    {
        return $this->oficioFile;
    }

    /**
     * @param File $oficioFile
     */
    public function setOficioFile($oficioFile)
    {
        $this->oficioFile = $oficioFile;

        $this->setFechaOficioAceptacion(Carbon::now());
    }

    /**
     * @return DateTime
     */
    public function getFechaOficioAceptacion()
    {
        return $this->fechaOficioAceptacion;
    }

    /**
     * @param DateTime $fechaOficioAceptacion
     */
    public function setFechaOficioAceptacion($fechaOficioAceptacion)
    {
        $this->fechaOficioAceptacion = $fechaOficioAceptacion;
    }

    public function getMonths()
    {
        $inicial = Carbon::instance($this->fechaInicio);
        $final = Carbon::instance($this->fechaTermino);

        return $final->diffInMonths($inicial, true);
    }

    /**
     * @return CargaMasiva
     */
    public function getCargaMasiva()
    {
        return $this->cargaMasiva;
    }

    /**
     * @param CargaMasiva $cargaMasiva
     * @return Residencia
     */
    public function setCargaMasiva(?CargaMasiva $cargaMasiva = null)
    {
        $this->cargaMasiva = $cargaMasiva;
        return $this;
    }


    public function setIsDelete($val)
    {
        $this->deleted_at = !!$val ? new \DateTime() : null;
    }

    public function getIsDelete()
    {
        return !!$this->deleted_at;
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

