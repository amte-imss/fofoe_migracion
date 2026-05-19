<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[ORM\Entity(repositoryClass: \App\Repository\ConvenioEspecialRepository::class)]
#[Assert\GroupSequenceProvider]
#[ORM\Table(name: 'convenio_especial')]
class ConvenioEspecial implements GroupSequenceProviderInterface, \Stringable
{
    const SECTOR_PUBLICO = "Público";
    const SECTOR_PRIVADO = "Privado";

    const TIPO_ESPECIAL = "Proyecto Especial";
    const TIPO_CAME = "Local";

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
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 250)]
    #[Assert\NotBlank]
    protected $sector;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 250)]
    #[Assert\NotBlank]
    public $tipo;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 25)]
    protected $tipo_came;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 25)]
    protected $rfc;

    /**
     * @var DateTime
     *
     */
    #[ORM\Column(type: 'date')]
    protected $vigencia;

    /**
     * @var CicloAcademico
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\CicloAcademico::class)]
    #[ORM\JoinColumn(name: 'ciclo_academico_id', referencedColumnName: 'id', nullable: true)]
    protected $cicloAcademico;

    /**
     * @var Institucion
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Institucion::class, inversedBy: 'convenios')]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank(message: 'Debe especificar una Institución válida que ya se encuentre registrada en el sistema.')]
    protected $institucion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'integer')]
    private $institucionId;


    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $numero;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $is_camex;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $fuica;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $razon_social;

    /**
     * @var DateTime
     *
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_vence_convenio;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_firma;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $objetivo_colaboracion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20)]
    protected $vigencia_text;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $recibe_signfirst;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $recibe_signsecond;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $cargo_emite_signfirst;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $cargo_emite_signsecond;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $nombre_emite_signfirst;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $nombre_emite_signsecond;

    /**
     * @var CoordinacionFirmante
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\CoordinacionFirmante::class)]
    #[ORM\JoinColumn(name: 'cargo_firma_idfirst', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: 'Debe especificar un valor del catálogo de Cargo de Firmantes.')]
    protected $cargoFirmaIdFirst;

    /**
     * @var CoordinacionFirmante
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\CoordinacionFirmante::class)]
    #[ORM\JoinColumn(name: 'cargo_firma_idsecond', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: 'Debe especificar un valor del catálogo de Cargo de Firmantes.')]
    protected $cargoFirmaIdSecond;

    /**
     * @var NivelAcademico
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\NivelAcademico::class)]
    #[ORM\JoinColumn(name: 'nivel_academico_id', referencedColumnName: 'id', nullable: true)]
    protected $nivelAcademico;

    /**
     * @var ConveniosNivelAcademico
     */
    #[ORM\Column(name: 'convenio_nivelacademico_id', type: 'integer')]
    #[ORM\OneToOne(targetEntity: \App\Entity\ConveniosNivelAcademico::class, mappedBy: 'convenioId')]
    protected $conveniosNivelAcademico;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $urlConvenio;

    #[ORM\OneToMany(targetEntity: \ConvenioEspecialDelegacion::class, mappedBy: 'convenio')]
    private $delegacionConvenios;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $todasDelegaciones;


    #[ORM\ManyToOne(targetEntity: \MotivoColaboracion::class, inversedBy: 'convenios')]
    #[ORM\JoinColumn(name: 'motivo_colaboracion_id', referencedColumnName: 'id', nullable: false)]
    private $motivoColaboracion;

    #[ORM\ManyToOne(targetEntity: \Recurso::class, inversedBy: 'convenios')]
    #[ORM\JoinColumn(name: 'recurso_id', referencedColumnName: 'id', nullable: false)]
    private $recurso;

    #[ORM\ManyToOne(targetEntity: \Poblacion::class, inversedBy: 'convenios')]
    #[ORM\JoinColumn(name: 'poblacion_id', referencedColumnName: 'id', nullable: false)]
    private $poblacion;

    public function __construct()
    {
        $this->delegacionConvenios = new ArrayCollection();
        $this->conveniosCiclosAcademicos = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @param string $sector
     * @return Convenio
     */
    public function setSector($sector)
    {
        $this->sector = $sector;

        return $this;
    }

    /**
     * @return string
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param string $tipo
     * @return Convenio
     */
    public function setTipo($tipo)
    {
        $this->tipo = self::TIPO_ESPECIAL;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param string $tipoCame
     * @return Convenio
     */
    public function setTipoCame($tipoCame)
    {
        $this->tipo_came = $tipoCame;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoCame()
    {
        return $this->tipo_came;
    }

    /**
     * @param string $rfc
     * @return Convenio
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * @return string
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * @param DateTime $vigencia
     * @return Convenio
     */
    public function setVigencia($vigencia)
    {
        $this->vigencia = $vigencia;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getVigencia()
    {
        return $this->vigencia;
    }

    /**
     * @param Institucion $institucion
     * @return Convenio
     */
    public function setInstitucion($institucion = null)
    {
        $this->institucion = $institucion;

        return $this;
    }

    /**
     * @return Institucion
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * @return integer
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @param boolean $isCamex
     * @return boolean
     */
    public function setIsCamex($isCamex)
    {
        $this->is_camex = $isCamex;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsCamex()
    {
        return $this->is_camex;
    }

    public function getLabel()
    {
        if ($this->vigencia > Carbon::now()->addMonths(12)) {
            return 'green';
        }
        if (
            $this->vigencia > Carbon::now()->addMonths(6) and
            $this->vigencia <= Carbon::now()->addMonths(12)
        ) {
            return 'yellow';
        }
        if (
            $this->vigencia >= Carbon::now() and
            $this->vigencia <= Carbon::now()->addMonths(6)
        ) {
            return 'orange';
        }

        return 'red';
    }

    public function getGroupSequence(): \Symfony\Component\Validator\Constraints\GroupSequence|array
    {
        return [
            'Convenio',
            $this->tipo,
        ];
    }


    public function __toString(): string
    {
        return $this->getNombre();
    }


    /**
     * @return string
     */
    public function getVigenciaFormatted()
    {
        return $this->vigencia->format('d/m/Y');
    }

    /**
     * @param string $fuica
     * @return Convenio
     */
    public function setFuica($fuica)
    {
        $this->fuica = $fuica;

        return $this;
    }

    /**
     * @return string
     */
    public function getFuica()
    {
        return $this->fuica;
    }

    /**
     * @param string $razonSocial
     * @return string
     */
    public function setRazonSocial($razonSocial)
    {
        $this->razon_social = $razonSocial;

        return $this;
    }

    /**
     * @return string
     */
    public function getRazonSocial()
    {
        return $this->razon_social;
    }

    /**
     * @param \DateTime $fechaVenceConvenio
     * @return Convenio
     */
    public function setFechaVenceConvenio($fechaVenceConvenio)
    {
        $this->fecha_vence_convenio = $fechaVenceConvenio;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaVenceConvenio()
    {
        return $this->fecha_vence_convenio;
    }

    public function getFechaVenceConvenioFormatted()
    {
        return $this->getFechaVenceConvenio() ? $this->getFechaVenceConvenio()->format('d-m-Y') : '';
    }

    /**
     * @param \DateTime $fechaFirma
     * @return Convenio
     */
    public function setFechaFirma($fechaFirma)
    {
        $this->fecha_firma = $fechaFirma;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaFirma()
    {
        return $this->fecha_firma;
    }

    public function getFechaFirmaFormatted()
    {
        return $this->fecha_firma ? $this->fecha_firma->format('d-m-Y') : '';
    }


    /**
     * @return string
     */
    public function getObjetivoColaboracion()
    {
        return $this->objetivo_colaboracion;
    }

    /**
     * @param string $objetivoColaboracion
     */
    public function setObjetivoColaboracion($objetivoColaboracion)
    {
        $this->objetivo_colaboracion = $objetivoColaboracion;
    }


    /**
     * @return string
     */
    public function getVigenciaText()
    {
        return $this->vigencia_text;
    }

    public function getVigenciaTextFormatted()
    {
        $vigencia = strtoupper($this->vigencia_text);
        if($vigencia === "1"){
            return "1 AÑO";
        }else if(str_contains($vigencia, 'YEAR')){
            return str_replace('YEAR', 'AÑO', $vigencia);
        }else{
            return $vigencia.' AÑOS';
        }
    }

    /**
     * @param string $vigenciaAnios
     */
    public function setVigenciaText($vigenciaAnios)
    {
        $this->vigencia_text = $vigenciaAnios;
    }

    /**
     * @param string $receptorFirmaUno
     */
    public function setRecibeSignfirst($receptorFirmaUno)
    {
        $this->recibe_signfirst = $receptorFirmaUno;
    }

    /**
     * @return string
     */
    public function getRecibeSignfirst()
    {
        return $this->recibe_signfirst;
    }

    /**
     * @param string $receptorFirmaDos
     */
    public function setRecibeSignsecond($receptorFirmaDos)
    {
        $this->recibe_signsecond = $receptorFirmaDos;
    }

    /**
     * @return string
     */
    public function getRecibeSignsecond()
    {
        return $this->recibe_signsecond;
    }

    /**
     * @param string $cargoEmiteFirmaUno
     */
    public function setCargoEmiteSignfirst($cargoEmiteFirmaUno)
    {
        $this->cargo_emite_signfirst = $cargoEmiteFirmaUno;
    }

    /**
     * @return string
     */
    public function getCargoEmiteSignfirst()
    {
        return $this->cargo_emite_signfirst;
    }

    /**
     * @param string $cargoEmiteFirmaDos
     */
    public function setCargoEmiteSignsecond($cargoEmiteFirmaDos)
    {
        $this->cargo_emite_signsecond = $cargoEmiteFirmaDos;
    }

    /**
     * @return string
     */
    public function getCargoEmiteSignsecond()
    {
        return $this->cargo_emite_signsecond;
    }

    /**
     * @param string $nombreEmiteFirmaUno
     */
    public function setNombreEmiteSignfirst($nombreEmiteFirmaUno)
    {
        $this->nombre_emite_signfirst = $nombreEmiteFirmaUno;
    }

    /**
     * @return string
     */
    public function getNombreEmiteSignfirst()
    {
        return $this->nombre_emite_signfirst;
    }

    /**
     * @param string $nombreEmiteFirmaDos
     */
    public function setNombreEmiteSignsecond($nombreEmiteFirmaDos)
    {
        $this->nombre_emite_signsecond = $nombreEmiteFirmaDos;
    }

    /**
     * @return string
     */
    public function getNombreEmiteSignsecond()
    {
        return $this->nombre_emite_signsecond;
    }

    /**
     * @param CoordinacionFirmante $cargoFirmaUno
     * @return Convenio
     */
    public function setCargoFirmaIdFirst(CoordinacionFirmante $cargoFirmaUno)
    {
        $this->cargoFirmaIdFirst = $cargoFirmaUno;

        return $this;
    }

    /**
     * @return CoordinacionFirmante
     */
    public function getCargoFirmaIdFirst()
    {
        return $this->cargoFirmaIdFirst;
    }

    /**
     * @param CoordinacionFirmante $cargoFirmaDos
     * @return Convenio
     */
    public function setCargoFirmaIdSecond(CoordinacionFirmante $cargoFirmaDos)
    {
        $this->cargoFirmaIdSecond = $cargoFirmaDos;

        return $this;
    }

    /**
     * @return CoordinacionFirmante
     */
    public function getCargoFirmaIdSecond()
    {
        return $this->cargoFirmaIdSecond;
    }

    /**
     * @param NivelAcademico $nivelAcademico
     * @return Convenio
     */
    public function setNivelAcademico(?NivelAcademico $nivelAcademico = null)
    {
        $this->nivelAcademico = $nivelAcademico;

        return $this;
    }

    /**
     * @return NivelAcademico
     */
    public function getNivelAcademico()
    {
        return $this->nivelAcademico;
    }


    /**
     * @param ConveniosNivelAcademico $convenioNivelAcademico
     * @return Convenio
     */
    public function setConveniosNivelAcademico(?ConveniosNivelAcademico $convenioNivelAcademico = null)
    {
        $this->conveniosNivelAcademico = $convenioNivelAcademico;

        return $this;
    }

    /**
     * @return ConveniosNivelAcademico
     */
    public function getConveniosNivelAcademico()
    {
        return $this->conveniosNivelAcademico;
    }

    /**
     * @return string
     */
    public function getInstitucionId()
    {
        return $this->institucionId;
    }

    /**
     * @param string $institucionId
     */
    public function setInstitucionId($institucionId)
    {
        $this->institucionId = $institucionId;
    }

    /**
     * @return string
     */
    public function getUrlConvenio()
    {
        return $this->urlConvenio;
    }

    /**
     * @param string $urlConvenio
     */
    public function setUrlConvenio($urlConvenio)
    {
        $this->urlConvenio = $urlConvenio;
    }


    /**
     * @return ArrayCollection
     */
    public function getDelegacionConvenios()
    {
        return $this->delegacionConvenios;
    }

    /**
     * @param ArrayCollection $delegacionConvenios
     */
    public function setDelegacionConvenios($delegacionConvenios)
    {
        $this->delegacionConvenios = $delegacionConvenios;
    }

    public function addConvenioDelegacion(ConvenioEspecialDelegacion $convenioDelegacion)
    {
        $this->delegacionConvenios[] = $convenioDelegacion;
        return $this;
    }

    public function removeConvenioDelegacion(ConvenioEspecialDelegacion $convenioDelegacion)
    {
        $this->delegacionConvenios->removeElement($convenioDelegacion);
    }

    /**
     * @return bool
     */
    public function isTodasDelegaciones()
    {
        return $this->todasDelegaciones;
    }

    /**
     * @param bool $todasDelegaciones
     */
    public function setTodasDelegaciones($todasDelegaciones)
    {
        $this->todasDelegaciones = $todasDelegaciones;
    }

    public function getConvenioDelegacionesFormatted()
    {
        $result = [];
        foreach ($this->delegacionConvenios as $delegacionConvenio){
            $result[]=$delegacionConvenio->getDelegacion()->getNombre();
        }
        if($this->isTodasDelegaciones()){
            return 'TODAS';
        }
        return implode(', ', $result);
    }

    public function getStatus()
    {
        $date = Carbon::parse($this->getFechaVenceConvenio()->format('Y-m-d'));

        if($date->greaterThanOrEqualTo(Carbon::now())){
            return 'active';
        }
        return 'inactive';
    }

    public function getMotivoColaboracion()
    {
        return $this->motivoColaboracion;
    }

    public function setMotivoColaboracion(MotivoColaboracion $motivoColaboracion)
    {
        $this->motivoColaboracion = $motivoColaboracion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurso()
    {
        return $this->recurso;
    }

    /**
     * @param mixed $recurso
     */
    public function setRecurso($recurso)
    {
        $this->recurso = $recurso;
    }

    /**
     * @return mixed
     */
    public function getPoblacion()
    {
        return $this->poblacion;
    }

    /**
     * @param mixed $poblacion
     */
    public function setPoblacion($poblacion)
    {
        $this->poblacion = $poblacion;
    }
}