<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: \App\Repository\ConvenioRepository::class)]
#[Assert\GroupSequenceProvider]
#[ORM\Table(name: 'convenio')]
class Convenio implements GroupSequenceProviderInterface, \Stringable
{
    const SECTOR_PUBLICO = "Público";
    const SECTOR_PRIVADO = "Privado";
    const TIPO_GENERAL = "General";
    const TIPO_ESPECIFICO = "Específico";
    const TIPO_CAME = "Local";

    const SECTORES = [self::SECTOR_PUBLICO, self::SECTOR_PRIVADO];
    const TIPOS = [self::TIPO_GENERAL, self::TIPO_ESPECIFICO];

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
    #[Assert\Choice(choices: Convenio::SECTORES, message: 'Sector debe ser Público o Privado')]
    protected $sector;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 250)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Convenio::TIPOS, message: 'Tipo debe ser General o Específico')]
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
     * @var Carrera
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Carrera::class, inversedBy: 'convenios')]
    #[ORM\JoinColumn(name: 'carrera_id', referencedColumnName: 'id', nullable: true)]
    protected $carrera;

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
     * @var Delegacion
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Delegacion::class)]
    #[ORM\JoinColumn(name: 'delegacion_id', referencedColumnName: 'id')]
    protected $delegacion;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $numero;

    /**
     * @var CampoClinico
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\CampoClinico::class, mappedBy: 'convenio')]
    protected $camposClinicos;

    /**
     * @var Convenio
     * se quita esta validacion porque no se entiend @ Assert\NotBlank(groups={Convenio::TIPO_ESPECIFICO},
     *   message="Los convenios específicos deben estar asociados a un convenio General.
     * Verifique que existe un convenio general para la institución."
     *  )
     */
    protected $general;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $is_camex;

    /**
     * @var Convenio
     *
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Convenio::class)]
    #[ORM\JoinColumn(name: 'convenio_general', referencedColumnName: 'id', nullable: true)]
    public $convenio_general;

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
    protected $fecha_ota;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_vence_ota;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_rvoe;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_vence_rvoe;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_firma;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_emite_comaem;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    protected $fecha_vence_comaem;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $objetivo_colaboracion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $nombre_programa;

    /**
     * @var Disciplina
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Disciplina::class)]
    #[ORM\JoinColumn(name: 'disciplina_id', referencedColumnName: 'id')]
    protected $disciplina;

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
     * @var ConveniosDisciplina
     */
    #[ORM\Column(name: 'convenio_disciplina_id', type: 'integer')]
    #[ORM\OneToOne(targetEntity: \App\Entity\ConveniosDisciplina::class, mappedBy: 'convenioId')]
    protected $conveniosDisciplina;

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

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $cartaIntencion;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $fechaCartaIntencion;

    #[ORM\OneToMany(targetEntity: \ConvenioDelegacion::class, mappedBy: 'convenio')]
    private $delegacionConvenios;

    #[ORM\OneToMany(targetEntity: \ConvenioCicloAcademico::class, mappedBy: 'convenio')]
    private $conveniosCiclosAcademicos;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $todasDelegaciones;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    protected $todosCiclosAcademicos;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $cancelacionAnticipada;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $isUmae;

    public function __construct()
    {
        $this->delegacionConvenios = new ArrayCollection();
        $this->conveniosCiclosAcademicos = new ArrayCollection();
        $this->cancelacionAnticipada = false;
        $this->isUmae = false;
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
        $this->tipo = $tipo;

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
     * @param CicloAcademico $cicloAcademico
     * @return Convenio
     */
    public function setCicloAcademico(?CicloAcademico $cicloAcademico = null)
    {
        $this->cicloAcademico = $cicloAcademico;

        return $this;
    }

    /**
     * @return CicloAcademico
     */
    public function getCicloAcademico()
    {
        return $this->cicloAcademico;
    }

    public function getCiclo()
    {
        return $this->getCicloAcademico();
    }

    /**
     * @param Carrera $carrera
     * @return Convenio
     */
    public function setCarrera(?Carrera $carrera = null)
    {
        $this->carrera = $carrera;
        return $this;
    }

    /**
     * @return Carrera
     */
    public function getCarrera()
    {
        return $this->carrera;
    }

    public function getGrado()
    {
        return $this->carrera ?
            $this->carrera->getNivelAcademico()->getNombre()
            : null;
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
     * @param Delegacion $delegacion
     * @return Convenio
     */
    public function setDelegacion(?Delegacion $delegacion = null)
    {
        $this->delegacion = $delegacion;

        return $this;
    }

    /**
     * @return Delegacion
     */
    public function getDelegacion()
    {
        return $this->delegacion;
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

    /**
     * @param Convenio $convenio
     * @return Convenio
     */
    public function setConvenioGeneral($convenio)
    {
        $this->convenio_general = $convenio;
        return $this;
    }

    /**
     * @return Convenio
     */
    public function getConvenioGeneral()
    {
        return $this->convenio_general;
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

    public function getCampoClinicos()
    {
        return $this->camposClinicos;
    }

    public function __toString(): string
    {
        return $this->getNombre();
    }

    /**
     * @return Convenio
     */
    public function getGeneral()
    {
        return $this->general;
    }

    /**
     * @param Convenio $general
     */
    public function setGeneral(?Convenio $general = null)
    {
        $this->general = $general;
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
     * @param \DateTime $fechaOta
     * @return Convenio
     */
    public function setFechaOta($fechaOta)
    {
        $this->fecha_ota = $fechaOta;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaOta()
    {
        return $this->fecha_ota;
    }

    public function getFechaOtaFormatted()
    {
        return $this->fecha_ota ? $this->getFechaOta()->format('d-m-Y') : '';
    }

    /**
     * @param \DateTime $fechaVenceOta
     * @return Convenio
     */
    public function setFechaVenceOta($fechaVenceOta)
    {
        $this->fecha_vence_ota = $fechaVenceOta;

        return $this;
    }

    public function getFechaVenceOtaFormatted()
    {
        return $this->fecha_vence_ota ? $this->getFechaVenceOta()->format('d-m-Y') : '';
    }

    /**
     * @return \DateTime
     */
    public function getFechaVenceOta()
    {
        return $this->fecha_vence_ota;
    }

    /**
     * @param \DateTime $fechaRvoe
     * @return Convenio
     */
    public function setFechaRvoe($fechaRvoe)
    {
        $this->fecha_rvoe = $fechaRvoe;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaRvoe()
    {
        return $this->fecha_rvoe;
    }

    public function getFechaRvoeFormatted()
    {
        return $this->fecha_rvoe ? $this->getFechaRvoe()->format('d-m-Y') : '';
    }

    /**
     * @param \DateTime $fechaVenceRvoe
     * @return Convenio
     */
    public function setFechaVenceRvoe($fechaVenceRvoe)
    {
        $this->fecha_vence_rvoe = $fechaVenceRvoe;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaVenceRvoe()
    {
        return $this->fecha_vence_rvoe;
    }

    public function getFechaVenceRvoeFormatted()
    {
        return $this->fecha_vence_rvoe ? $this->getFechaVenceRvoe()->format('d-m-Y') : '';
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
     * @param \DateTime $fechaEmiteComaem
     * @return Convenio
     */
    public function setFechaEmiteComaem($fechaEmiteComaem)
    {
        $this->fecha_emite_comaem = $fechaEmiteComaem;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaEmiteComaem()
    {
        return $this->fecha_emite_comaem;
    }

    public function getFechaEmiteComaemFormatted()
    {
        return $this->fecha_emite_comaem ? $this->getFechaEmiteComaem()->format('d-m-Y') : '';
    }

    /**
     * @param \DateTime $fechaFirma
     * @return Convenio
     */
    public function setFechaVenceComaem($fechaVenceComaem)
    {
        $this->fecha_vence_comaem = $fechaVenceComaem;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaVenceComaem()
    {
        return $this->fecha_vence_comaem;
    }

    public function getFechaVenceComaemFormatted()
    {
        return $this->fecha_vence_comaem ? $this->getFechaVenceComaem()->format('d-m-Y') : '';
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
     * @param Disciplina $disciplina
     * @return Convenio
     */
    public function setDisciplina(Disciplina $disciplina)
    {
        $this->disciplina = $disciplina;

        return $this;
    }

    /**
     * @return Disciplina
     */
    public function getDisciplina()
    {
        return $this->disciplina;
    }

    /**
     * @return string
     */
    public function getNombrePrograma()
    {
        return $this->nombre_programa;
    }

    /**
     * @param string $nombrePrograma
     */
    public function setNombrePrograma($nombrePrograma)
    {
        $this->nombre_programa = $nombrePrograma;
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
        if ($vigencia === "1") {
            return "1 AÑO";
        } else if (str_contains($vigencia, 'YEAR')) {
            return str_replace('YEAR', 'AÑO', $vigencia);
        } else {
            return $vigencia . ' AÑOS';
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
     * @param ConveniosDisciplina $convenioDisciplina
     * @return Convenio
     */
    public function setConveniosDisciplina(?ConveniosDisciplina $convenioDisciplina = null)
    {
        $this->conveniosDisciplina = $convenioDisciplina;

        return $this;
    }

    /**
     * @return ConveniosDisciplina
     */
    public function getConveniosDisciplina()
    {
        return $this->conveniosDisciplina;
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
     * @return boolean
     */
    public function isCartaIntencion()
    {
        return $this->cartaIntencion;
    }

    /**
     * @param boolean $cartaIntencion
     */
    public function setCartaIntencion($cartaIntencion)
    {
        $this->cartaIntencion = $cartaIntencion;
    }

    /**
     * @return DateTime
     */
    public function getFechaCartaIntencion()
    {
        return $this->fechaCartaIntencion;
    }

    /**
     * @param DateTime $fechaCartaIntencion
     */
    public function setFechaCartaIntencion($fechaCartaIntencion)
    {
        $this->fechaCartaIntencion = $fechaCartaIntencion;
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

    public function addConvenioDelegacion(ConvenioDelegacion $convenioDelegacion)
    {
        $this->delegacionConvenios[] = $convenioDelegacion;
        return $this;
    }

    public function removeConvenioDelegacion(ConvenioDelegacion $convenioDelegacion)
    {
        $this->delegacionConvenios->removeElement($convenioDelegacion);
    }

    /**
     * @return ArrayCollection
     */
    public function getConveniosCiclosAcademicos()
    {
        return $this->conveniosCiclosAcademicos;
    }

    /**
     * @param ArrayCollection $conveniosCiclosAcademicos
     */
    public function setConveniosCiclosAcademicos($conveniosCiclosAcademicos)
    {
        $this->conveniosCiclosAcademicos = $conveniosCiclosAcademicos;
    }

    public function addConvenioCicloAcademico(ConvenioCicloAcademico $convenioCicloAcademico)
    {
        $this->conveniosCiclosAcademicos[] = $convenioCicloAcademico;
        return $this;
    }

    public function removeConvenioCicloAcademico(ConvenioCicloAcademico $convenioCicloAcademico)
    {
        $this->conveniosCiclosAcademicos->removeElement($convenioCicloAcademico);
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

    /**
     * @return bool
     */
    public function isTodosCiclosAcademicos()
    {
        return $this->todosCiclosAcademicos;
    }

    /**
     * @param bool $todosCiclosAcademicos
     */
    public function setTodosCiclosAcademicos($todosCiclosAcademicos)
    {
        $this->todosCiclosAcademicos = $todosCiclosAcademicos;
    }

    public function getIndicadorFechaVenceComaem()
    {
        $result = '';
        if (is_null($this->getFechaVenceComaem())) {
            return $result;
        }
        $fechaFin = Carbon::parse($this->getFechaVenceComaem()->format('Y-m-d'));
        $now = Carbon::now();
        if ($now->lessThanOrEqualTo($fechaFin->copy()->subYear())) {
            $result = 'expiredValiditySuccess';
        } else if ($now->lessThanOrEqualTo($fechaFin->copy()->subMonths(6))) {
            $result = 'expiredValidityWarn';
        } else {
            $result = 'expiredValidityDanger';
        }
        return $result;
    }

    public function getIndicadorFechaVenceRvoe()
    {
        $result = '';
        if (is_null($this->getFechaVenceRvoe())) {
            return $result;
        }
        $fechaFin = Carbon::parse($this->getFechaVenceRvoe()->format('Y-m-d'));
        $now = Carbon::now();
        if ($now->lessThanOrEqualTo($fechaFin->copy()->subYear())) {
            $result = 'expiredValiditySuccess';
        } else if ($now->lessThanOrEqualTo($fechaFin->copy()->subMonths(6))) {
            $result = 'expiredValidityWarn';
        } else {
            $result = 'expiredValidityDanger';
        }
        return $result;
    }


    public function getIndicadorFechaVenceOta()
    {

        $result = '';
        if (is_null($this->getFechaVenceOta())) {
            return $result;
        }
        $fechaFin = Carbon::parse($this->getFechaVenceOta()->format('Y-m-d'));
        $now = Carbon::now();
        if ($now->lessThanOrEqualTo($fechaFin->copy()->subYear())) {
            $result = 'expiredValiditySuccess';
        } else if ($now->lessThanOrEqualTo($fechaFin->copy()->subMonths(6))) {
            $result = 'expiredValidityWarn';
        } else {
            $result = 'expiredValidityDanger';
        }
        return $result;

    }

    public function getIndicadorFechaVigencia()
    {
        $result = '';
        if (is_null($this->getVigencia())) {
            return $result;
        }
        $fechaFin = Carbon::parse($this->getVigencia()->format('Y-m-d'));
        $now = Carbon::now();
        if ($now->lessThanOrEqualTo($fechaFin->copy()->subYear())) {
            $result = 'expiredValiditySuccess';
        } else if ($now->lessThanOrEqualTo($fechaFin->copy()->subMonths(6))) {
            $result = 'expiredValidityWarn';
        } else {
            $result = 'expiredValidityDanger';
        }
        return $result;

    }

    public function getIndicadorCartaVigencia()
    {
        if ($this->isCartaIntencion() && $this->getFechaCartaIntencion()) {
            $result = '';
            $fechaFin = Carbon::parse($this->getFechaCartaIntencion()->format('Y-m-d'));
            $now = Carbon::now();
            if ($now->lessThanOrEqualTo($fechaFin->copy()->subYear())) {
                $result = 'expiredValiditySuccess';
            } else if ($now->lessThanOrEqualTo($fechaFin->copy()->subMonths(6))) {
                $result = 'expiredValidityWarn';
            } else {
                $result = 'expiredValidityDanger';
            }
            return $result;
        }
        return '';
    }

    public function getCartaVigenciaData()
    {
        if ($this->isCartaIntencion()) {
            return $this->getFechaCartaIntencion() ? $this->getFechaCartaIntencion()->format('d-m-Y') : '';
        }
        return 'N/A';
    }

    public function getConvenioDelegacionesFormatted()
    {
        $result = [];
        foreach ($this->delegacionConvenios as $delegacionConvenio) {
            $result[] = $delegacionConvenio->getDelegacion()->getNombre();
        }
        if ($this->isTodasDelegaciones()) {
            return 'TODAS';
        }
        return implode(', ', $result);
    }

    public function getCiclosAcademicosFormatted()
    {
        $result = [];
        foreach ($this->conveniosCiclosAcademicos as $conveniosCiclosAcademico) {
            $result[] = $conveniosCiclosAcademico->getCicloAcademico()->getNombre();
        }
        return implode(', ', $result);
    }

    /**
     * @return mixed
     */
    public function isCancelacionAnticipada()
    {
        return $this->cancelacionAnticipada;
    }

    /**
     * @param mixed $cancelacionAnticipada
     */
    public function setCancelacionAnticipada($cancelacionAnticipada)
    {
        $this->cancelacionAnticipada = $cancelacionAnticipada;
    }

    public function getStatus()
    {
        $date = Carbon::parse($this->getFechaVenceConvenio()->format('Y-m-d'));
        if ($this->isCancelacionAnticipada()) {
            return 'canceled';
        }
        if ($date->greaterThanOrEqualTo(Carbon::now()) && !$this->isCancelacionAnticipada()) {
            return 'active';
        }
        return 'inactive';
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'nombre' => $this->getNombre(),
            'sector' => $this->getSector(),
            'tipo' => $this->getTipo(),
            'tipo_came' => $this->getTipoCame(),
            'rfc' => $this->getRfc(),
            'vigencia' => $this->getVigencia() ? $this->getVigencia()->format('Y-m-d') : null,
            'fuica' => $this->getFuica(),
            'razon_social' => $this->getRazonSocial(),
            'fecha_vence_convenio' => $this->getFechaVenceConvenioFormatted(),
            'fecha_ota' => $this->getFechaOtaFormatted(),
            'fecha_vence_ota' => $this->getFechaVenceOtaFormatted(),
            'fecha_rvoe' => $this->getFechaRvoeFormatted(),
            'fecha_vence_rvoe' => $this->getFechaVenceRvoeFormatted(),
            'fecha_firma' => $this->getFechaFirmaFormatted(),
            'fecha_emite_comaem' => $this->getFechaEmiteComaemFormatted(),
            'fecha_vence_comaem' => $this->getFechaVenceComaemFormatted(),
            'objetivo_colaboracion' => $this->getObjetivoColaboracion(),
            'nombre_programa' => $this->getNombrePrograma(),
            'vigencia_text' => $this->getVigenciaTextFormatted(),
            'recibe_signfirst' => $this->getRecibeSignfirst(),
            'recibe_signsecond' => $this->getRecibeSignsecond(),
            'cargo_emite_signfirst' => $this->getCargoEmiteSignfirst(),
            'cargo_emite_signsecond' => $this->getCargoEmiteSignsecond(),
            'nombre_emite_signfirst' => $this->getNombreEmiteSignfirst(),
            'nombre_emite_signsecond' => $this->getNombreEmiteSignsecond(),
            'url_convenio' => $this->getUrlConvenio(),
            'carta_intencion' => $this->isCartaIntencion(),
            'fecha_carta_intencion' => $this->getFechaCartaIntencion() ? $this->getFechaCartaIntencion()->format('Y-m-d') : null,
            'todas_delegaciones' => $this->isTodasDelegaciones(),
            'todos_ciclos_academicos' => $this->isTodosCiclosAcademicos(),
            'cancelacion_anticipada' => $this->isCancelacionAnticipada(),
            'status' => $this->getStatus(),
            'is_camex' => $this->getIsCamex(),

            // Relaciones (se usa null si no existen)
            'institucion' => $this->getInstitucion() ? $this->getInstitucion()->getNombre() : null,
            'institucion_id' => $this->getInstitucionId(),
            'carrera' => $this->getCarrera() ? $this->getCarrera()->getNombre() : null,
            'delegacion' => $this->getDelegacion() ? $this->getDelegacion()->getNombre() : null,
            'disciplina' => $this->getDisciplina() ? $this->getDisciplina()->getNombre() : null,
            'nivel_academico' => $this->getNivelAcademico() ? $this->getNivelAcademico()->getNombre() : null,
            'cargo_firma_first' => $this->getCargoFirmaIdFirst() ? $this->getCargoFirmaIdFirst()->getNombre() : null,
            'cargo_firma_second' => $this->getCargoFirmaIdSecond() ? $this->getCargoFirmaIdSecond()->getNombre() : null,
            'convenio_general_id' => $this->getConvenioGeneral() ? $this->getConvenioGeneral()->getId() : null,

            // Colecciones como arreglos de texto
            'delegaciones' => $this->getConvenioDelegacionesFormatted(),
            'ciclos_academicos' => $this->getCiclosAcademicosFormatted(),

            // Indicadores de fecha
            'indicador_vigencia' => $this->getIndicadorFechaVigencia(),
            'indicador_rvoe' => $this->getIndicadorFechaVenceRvoe(),
            'indicador_ota' => $this->getIndicadorFechaVenceOta(),
            'indicador_comaem' => $this->getIndicadorFechaVenceComaem(),
            'indicador_carta' => $this->getIndicadorCartaVigencia(),
            'carta_vigencia_data' => $this->getCartaVigenciaData()
        ];
    }

    /**
     * @return bool
     */
    public function isUmae()
    {
        return $this->isUmae;
    }

    /**
     * @param bool $isUmae
     */
    public function setIsUmae($isUmae)
    {
        $this->isUmae = $isUmae;
    }

}
