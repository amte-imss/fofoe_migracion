<?php

namespace App\Entity;

use App\Repository\PagoRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\CampoClinicoRepository::class)]
#[UniqueEntity(fields: ['solicitud', 'fechaInicial', 'fechaFinal', 'convenio', 'unidad', 'asignatura'], errorPath: 'convenio', message: 'Ya registró un campo clínico para ese período, sede y asignatura.')]
#[ORM\Table(name: 'campo_clinico')]
class CampoClinico implements ReferenciaBancariaInterface, \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    #[ORM\Column(type: 'date')]
    #[Assert\GreaterThanOrEqual(value: 'today', groups: ['regIE'])]
    private $fechaInicial;

    #[ORM\Column(type: 'date')]
    private $fechaFinal;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $horario;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $promocion;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThan(value: 0)]
    private $lugaresSolicitados;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $lugaresAutorizados;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $formatoFofoeFileName;

    /**
     * @var File
     * @Vich\UploadableField(mapping="formatos_fofoe", fileNameProperty="formatoFofoeFileName")
     */
    #[Assert\File(maxSize: '2M', mimeTypes: ['application/pdf', 'application/x-pdf'], mimeTypesMessage: 'Sólo se admiten archivos PDF', maxSizeMessage: 'El archivo es muy grande  ({{ size }} {{ suffix }}). El tamaño máximo permitido es {{ limit }} {{ suffix }}.')]
    private $formatoFofoeFile;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Convenio::class, inversedBy: 'camposClinicos')]
    #[ORM\JoinColumn(name: 'convenio_id', referencedColumnName: 'id')]
    private $convenio;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $referenciaBancaria;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
    private $monto;

    /**
     * @var Solicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Solicitud::class, inversedBy: 'camposClinicos')]
    #[ORM\JoinColumn(name: 'solicitud_id', referencedColumnName: 'id')]
    private $solicitud;

    #[ORM\OneToOne(targetEntity: \App\Entity\MontoCarrera::class, mappedBy: 'campoClinico', cascade: ['persist'])]
    private $montoCarrera;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $observaciones;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $obsValRegistro;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EstatusCampo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'estatus_campo_id', referencedColumnName: 'id')]
    private $estatus;

    /**
     * @var Unidad
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Unidad::class, inversedBy: 'camposClinicos')]
    #[ORM\JoinColumn(name: 'unidad_id', referencedColumnName: 'id')]
    private $unidad;

    #[ORM\Column(type: 'string', length: 1500, nullable: true)]
    private $asignatura;

    #[ORM\OneToMany(targetEntity: \App\Entity\TrabajadorImss::class, mappedBy: 'campoClinico', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $trabajadoresBecados;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $fechaFormatoFofoe;

    #[ORM\ManyToOne(targetEntity: \App\Entity\CicloAcademico::class, inversedBy: 'camposClinicos')]
    #[ORM\JoinColumn(name: 'ciclo_academico_id', referencedColumnName: 'id')]
    private $cicloAcademico;


    #[ORM\Column(type: 'smallint', nullable: true)]
    protected $validateFormatoFofoe;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $motiveFormatoFofoe;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'solicitud_id')]
    private $solicitudId;


    public function __construct()
    {
        $this->trabajadoresBecados = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTime $fechaInicial
     * @return CampoClinico
     */
    public function setFechaInicial($fechaInicial)
    {
        $this->fechaInicial = $fechaInicial;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFechaInicial()
    {
        return $this->fechaInicial;
    }

    public function getDisplayFechaInicial() {
      return $this->fechaInicial->format('d/m/Y');
    }

    /**
     * @param DateTime $fechaFinal
     * @return CampoClinico
     */
    public function setFechaFinal($fechaFinal)
    {
        $this->fechaFinal = $fechaFinal;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFechaFinal()
    {
        return $this->fechaFinal;
    }

  public function getDisplayFechaFinal() {
    return $this->fechaFinal->format('d/m/Y');
  }

    /**
     * @param string $horario
     * @return CampoClinico
     */
    public function setHorario($horario)
    {
        $this->horario = $horario;

        return $this;
    }

    /**
     * @return string
     */
    public function getHorario()
    {
        return $this->horario;
    }

    /**
     * @param string $promocion
     * @return CampoClinico
     */
    public function setPromocion($promocion)
    {
        $this->promocion = $promocion;

        return $this;
    }

    /**
     * @return string
     */
    public function getPromocion()
    {
        return $this->promocion;
    }

    /**
     * @param integer $lugaresSolicitados
     * @return CampoClinico
     */
    public function setLugaresSolicitados($lugaresSolicitados)
    {
        $this->lugaresSolicitados = $lugaresSolicitados;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLugaresSolicitados()
    {
        return $this->lugaresSolicitados;
    }

    /**
     * @param integer $lugaresAutorizados
     * @return CampoClinico
     */
    public function setLugaresAutorizados($lugaresAutorizados)
    {
        $this->lugaresAutorizados = $lugaresAutorizados;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLugaresAutorizados()
    {
        return $this->lugaresAutorizados;
    }

    /**
     * @param string $referenciaBancaria
     * @return CampoClinico
     */
    public function setReferenciaBancaria($referenciaBancaria)
    {
        $this->referenciaBancaria = $referenciaBancaria;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenciaBancaria()
    {
        return $this->getSolicitud()->getTipoPago() === Solicitud::TIPO_PAGO_UNICO ?
            $this->getSolicitud()->getReferenciaBancaria() : $this->referenciaBancaria;
    }

    /**
     * @param float $monto
     * @return CampoClinico
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
     * @param Convenio $convenio
     * @return CampoClinico
     */
    public function setConvenio(?Convenio $convenio = null)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return Convenio
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    public function getNombreCicloAcademico() {
      return $this->getCicloAcademico() ? $this->getCicloAcademico()->getNombre() : "";
    }

    /**
     * @return Carrera|null
     */
    public function getCarrera() {
        return $this->convenio ? $this->convenio->getCarrera() : null;
    }

    /**
     * @return string
     */
    public function getDisplayCarrera() {
      $carrera = $this->convenio ?
        $this->convenio->getCarrera() : null;

      return $carrera ?
        $carrera->getDisplayName() : "";
    }

    /**
     * @param Solicitud $solicitud
     * @return CampoClinico
     */
    public function setSolicitud(?Solicitud $solicitud = null)
    {
        $this->solicitud = $solicitud;

        return $this;
    }

    /**
     * @return Solicitud
     */
    public function getSolicitud()
    {
        return $this->solicitud;
    }

    /**
     * @param EstatusCampo $estatus
     * @return void
     */
    public function setEstatus(?EstatusCampo $estatus = null)
    {
        $this->estatus = $estatus;
    }

    /**
     * @param Unidad $unidad
     * @return CampoClinico
     */
    public function setUnidad(?Unidad $unidad = null)
    {
        $this->unidad = $unidad;
        return $this;
    }

    /**
     * @return EstatusCampo
     */
    public function getEstatus()
    {
        return $this->estatus;
    }

    /**
      * @return Unidad
     */
    public function getUnidad()
    {
        return $this->unidad;
    }

    public function getWeeks()
    {
        $inicial = Carbon::instance($this->fechaInicial);
        $final = Carbon::instance($this->fechaFinal);

        $dias = 1 + $final->diffInDays($inicial);
        $weeks = intval($dias/7) + ($dias % 7 > 0 ? 1 : 0);

        return $this->lugaresAutorizados > 0 ? $weeks : 0;
    }


    /**
     * @param string $asignatura
     * @return CampoClinico
     */
    public function setAsignatura($asignatura)
    {
        $this->asignatura = $asignatura;

        return $this;
    }

    /**
     * @return string
     */
    public function getAsignatura()
    {
        return $this->asignatura;
    }

    /**
     * @return string
     */
    public function getFechaInicialFormatted()
    {
        return $this->getFechaInicial()->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getFechaFinalFormatted()
    {
        return $this->getFechaFinal()->format('d/m/Y');
    }
    /**
     * @return string
     */
    public function getFechaInicialYear()
    {
        return $this->getFechaInicial()->format('Y');
    }

    /**
     * @return string
     */
    public function getStatusCampo()
    {
        $actual = Carbon::now();
        $final = Carbon::instance($this->fechaFinal);

        if ($final < $actual){
            $status = "vigente";
        }else{
            $status = "finalizado";
        }

        return $status;
    }

    /**
     * Devuelve el estatus unificado para la API FOFOE.
     *
     * Prioridades:
     * 1. EstatusCampo = 'Pago no válido'             → "Pago no válido"
     * 2. Solicitud en REGISTRADA                     → "Pendiente de validar"
     * 3. Solicitud en EN_VALIDACION_FOFOE con docs
     *    rechazados (validateFormatoFofoe === 0)      → "Documentos rechazados"
     * 4. Resto de casos                              → getEstatusIEFormatted()
     *
     * Estatus posibles resultantes:
     *   Pendiente de validar | Solicitud revisada | En validación de montos
     *   Montos incorrectos   | Montos validados   | En validación FOFOE
     *   Documentos rechazados| Pago no válido     | Solicitud Pagada
     *
     * @return string|null
     */
    public function getEstatusFofoe()
    {
        // 1. Pago no válido viene del EstatusCampo
        $estatus = $this->getEstatus();
        if ($estatus && $estatus->getNombre() === 'Pago no válido') {
            return $estatus->getNombre();
        }

        $solicitud = $this->getSolicitud();
        if (!$solicitud) {
            return null;
        }

        // 2. Pendiente de validar
        if ($solicitud->getEstatus() === \App\Entity\SolicitudInterface::REGISTRADA) {
            return 'Pendiente de validar';
        }

        // 3. Documentos rechazados: EN_VALIDACION_FOFOE con algún campo con error
        if ($solicitud->getEstatus() === \App\Entity\SolicitudInterface::EN_VALIDACION_FOFOE) {
            $hasError = false;
            foreach ($solicitud->getCampoClinicos() as $campo) {
                if (!is_null($campo->getValidateFormatoFofoe()) && $campo->getValidateFormatoFofoe() === 0) {
                    $hasError = true;
                    break;
                }
            }
            if ($solicitud->getValidateOficioMontos() === 0 || $hasError) {
                return 'Documentos rechazados';
            }
        }

        // 4. Resto de casos
        return $solicitud->getEstatusIEFormatted();
    }


    /**
     * @return float|null
     */
    public function getMontoInscripcion()
    {
        $result = null;
        if ($this->getMontoCarrera()) {
          $result =$this->getMontoCarrera()->getMontoInscripcion();
        }
        return $result;
    }

    /**
     * @return float|null
     */
    public function getMontoColegiatura()
    {
        $result = null;
        if ($this->getMontoCarrera()) {
          $result =$this->getMontoCarrera()->getMontoColegiatura();
        }
        return $result;
    }

    /**
     * @return Collection|null
     */
    public function getDescuentos()
    {
        return $this->getMontoCarrera() ?
          $this->getMontoCarrera()->getDescuentos() : null;
    }

    /**
     * @return float
     */
    public function getImporteColegiaturaAnualIntegrada()
    {
        return $this->getMontoColegiatura() + $this->getMontoInscripcion();
    }

    /**
     * @return float
     */
    public function getFactorSemanalAutorizado()
    {
        return $this->getCicloAcademico()->getId() == 1 ? .005 : 0.5;
    }

    /**
     * @return float
     */
    public function getImporteAlumno()
    {
      return round($this->getImporteColegiaturaAnualIntegrada() * $this->getFactorSemanalAutorizado(), 2);
    }

    /**
     * @return float|int
     */
    public function getSubTotal()
    {
        if($this->getCicloAcademico()->getId() == 1) {
            return $this->getImporteAlumno() * $this->getLugaresAutorizados() * $this->getWeeks();
        }
        return $this->getImporteAlumno() * $this->getLugaresAutorizados();
    }

    public function getPago()
    {
        return $this->getPagos()->first();
    }

    public function  getLastPago()
    {
        return  $this->getPagos()->last();
    }

    public function getTiempoPago() {
        $lastPago = $this->getLastPago();
        if (!$lastPago) return -1000;
        $fechaInicio = $this->getFechaInicial();
        $inicial = Carbon::instance($fechaInicio);
        $final = $lastPago->getFechaPago() ?
            Carbon::instance($lastPago->getFechaPago()) : '';

        return $lastPago->getFechaPago() ?
            $final->diffInDays($inicial)*(
                $final->lessThanOrEqualTo($inicial)  ? 1 : -1)
            : -1000;
    }

    public function getPagos()
    {
        $criteria = PagoRepository::createGetPagoByReferenciaBancariaCriteria($this->getReferenciaBancaria());
        return $this->getSolicitud()->getPagos()->matching($criteria);
    }

    public function getDisplayDelegacion() {
      return $this->unidad ?
        $this->unidad
          ->getDelegacion()
          ->getNombre()
        : '';
    }

    public function getDisplayCicloAcademico() {
        return $this->getCicloAcademico() ? $this->getCicloAcademico()->getNombre() : "";
    }

  /**
   * @return MontoCarrera
   */
  public function getMontoCarrera()
  {
    return $this->montoCarrera;
  }

  /**
   * @param mixed $montoCarrera
   */
  public function setMontoCarrera($montoCarrera)
  {
    $this->montoCarrera = $montoCarrera;
  }

  /**
   * @return mixed
   */
  public function getObservaciones()
  {
    return $this->observaciones;
  }

  /**
   * @param string $observaciones
   * @return CampoClinico
   */
  public function setObservaciones($observaciones)
  {
    $this->observaciones = $observaciones;

    return $this;
  }

    /**
     * @return string
     */
    public function getObsValRegistro()
    {
        return $this->obsValRegistro;
    }

    /**
     * @param string $obsValRegistro
     * @return CampoClinico
     */
    public function setObsValRegistro($obsValRegistro)
    {
        $this->obsValRegistro = $obsValRegistro;

        return $this;
    }

    /**
     * @param TrabajadorImss $trabajadorImss
     * @return CampoClinico
     */
    public function addTrabajadoresBecado(TrabajadorImss $trabajadorImss)
    {
        if (!$this->trabajadoresBecados->contains($trabajadorImss)) {
            $this->trabajadoresBecados[] = $trabajadorImss;
            $trabajadorImss->setCampoClinico($this);
        }

        return $this;
    }

    /**
     * @param CampoClinico $camposClinico
     */
    public function removeTrabajadoresBecado(TrabajadorImss $trabajadorImss)
    {
        $this->trabajadoresBecados->removeElement($trabajadorImss);
    }

    /**
     * @return Collection
     */
    public function getTrabajadoresBecados()
    {
        return $this->trabajadoresBecados;
    }

    /**
     * @return integer
     */
    public function getTotalTrabajadoresBecados()
    {
        return $this->trabajadoresBecados->count();
    }

    /**
     * @param Collection
     * @return CampoClinico
     */
    public function setTrabajadoresBecados($trabajadoresBecados)
    {
        $this->trabajadoresBecados->clear();
        foreach ($trabajadoresBecados as $trabajadoresBecado) {
            $this->addTrabajadoresBecado($trabajadoresBecado);
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getFormatoFofoeFile()
    {
        return $this->formatoFofoeFile;
    }

    /**
     * @param File $formatoFofoeFile
     */
    public function setFormatoFofoeFile($formatoFofoeFile)
    {
        $this->formatoFofoeFile = $formatoFofoeFile;

        $this->setFechaFormatoFofoe(Carbon::now());
    }

    /**
     * @return string
     */
    public function getFormatoFofoeFileName()
    {
        return $this->formatoFofoeFileName;
    }

    /**
     * @param string $formatoFofoeFileName
     */
    public function setFormatoFofoeFileName($formatoFofoeFileName)
    {
        $this->formatoFofoeFileName = $formatoFofoeFileName;
    }

    /**
     * @return mixed
     */
    public function getFechaFormatoFofoe()
    {
        return $this->fechaFormatoFofoe;
    }

    /**
     * @param mixed $fechaFormatoFofoe
     */
    public function setFechaFormatoFofoe($fechaFormatoFofoe)
    {
        $this->fechaFormatoFofoe = $fechaFormatoFofoe;
    }

    public function getDelegacionUmae() {
        $unidad = $this->getUnidad();
        return $unidad->getEsUmae() ? $unidad : $unidad->getDelegacion();
    }

    public function __toString(): string
    {
        return ''
                .$this->getConvenio()
            .' - '
            . $this->getFechaInicialFormatted()
            .' al '.$this->getFechaFinalFormatted()
            .' - '
            .$this->getUnidad()->__toString()
            ;
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

    /**
     * @return mixed
     */
    public function getValidateFormatoFofoe()
    {
        return $this->validateFormatoFofoe;
    }

    /**
     * @param mixed $validateFormatoFofoe
     */
    public function setValidateFormatoFofoe($validateFormatoFofoe)
    {
        $this->validateFormatoFofoe = $validateFormatoFofoe;
    }

    /**
     * @return mixed
     */
    public function getMotiveFormatoFofoe()
    {
        return $this->motiveFormatoFofoe;
    }

    /**
     * @param mixed $motiveFormatoFofoe
     */
    public function setMotiveFormatoFofoe($motiveFormatoFofoe)
    {
        $this->motiveFormatoFofoe = $motiveFormatoFofoe;
    }

    public function getDisplayFormatted()
    {
        return self::__toString();
    }

    public function getSolicitudId()
    {
        return $this->solicitudId;
    }
}
